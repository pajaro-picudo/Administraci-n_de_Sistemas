#!/usr/bin/perl

use strict;
use warnings;
use JSON;
use File::Copy qw(copy);
use POSIX qw(getpwnam getgrnam);

# Rutas de archivos y directorios
my $cola = '/var/cola_usuarios/alta_usuarios.queue';
my $base_web = '/var/www/bioinformatica/public_html/usuarios';
my $instrucciones_txt = '/etc/skel/instrucciones.txt';
my $plantilla_html = '/etc/skel/index.html';

while (1) {
    if (-s $cola) {
        open(my $fh, '<', $cola) or die "No se pudo abrir la cola: $!";
        my @lineas = <$fh>;
        close($fh);

        foreach my $linea (@lineas) {
            chomp $linea;
            my $solicitud = eval { decode_json($linea) };
            unless ($solicitud) {
                warn "Error al decodificar JSON: $@\n";
                next;
            }

            my $usuario    = $solicitud->{usuario};
            my $nombre     = $solicitud->{nombre};
            my $contrasena = $solicitud->{contrasena};
            my $grupo      = $solicitud->{grupo} || 'usuarios';

            # Crear usuario con home y bash
            my $exit = system("useradd", "-m", "-s", "/bin/bash", "-g", $grupo, $usuario);
            if ($exit != 0) {
                warn "Error al crear el usuario $usuario con useradd\n";
                next;
            }

            # Crear Maildir con Dovecot
            system("runuser", "-l", $usuario, "-c", "maildirmake.dovecot ~/Maildir");

            # Corregir propietario y permisos de Maildir recursivamente
            my $home_dir = "/home/$usuario";
            my $maildir = "$home_dir/Maildir";

            if (-d $maildir) {
                my $uid = getpwnam($usuario);
                my $gid = getgrnam($grupo);
                if (defined $uid && defined $gid) {
                    system("chown", "-R", "$uid:$gid", $maildir);
                    system("chmod", "-R", "700", $maildir);
                } else {
                    warn "No se pudo obtener UID o GID para $usuario o $grupo\n";
                }
            } else {
                warn "Maildir no existe para $usuario\n";
            }

            # Asignar contraseña
            open(my $pw, '|-', 'chpasswd') or do {
                warn "No se pudo abrir chpasswd para $usuario\n";
                next;
            };
            print $pw "$usuario:$contrasena\n";
            close($pw);

            # Copiar instrucciones al home del usuario
            if (-e $instrucciones_txt) {
                copy($instrucciones_txt, "$home_dir/instrucciones.txt") or warn "Error copiando instrucciones.txt\n";
            }

            # Crear directorio web del usuario
            my $web_dir = "$base_web/$usuario";
            mkdir($web_dir, 0755) unless -d $web_dir;
            if (-e $plantilla_html) {
                copy($plantilla_html, "$web_dir/index.html") or warn "Error copiando index.html\n";
            }

            # Establecer propietario correcto del directorio web y otros ficheros
            my $uid = getpwnam($usuario);
            my $gid = getgrnam($grupo);
            if (defined $uid && defined $gid) {
                chown($uid, $gid, "$web_dir");
                chown($uid, $gid, "$web_dir/index.html") if -e "$web_dir/index.html";
                chown($uid, $gid, "$home_dir/instrucciones.txt") if -e "$home_dir/instrucciones.txt";
            }

            # Establecer cuota     
            my $soft_limit_kb = 90 * 1024;
            my $hard_limit_kb = 100 * 1024;
            my $comando = "setquota -u $usuario $soft_limit_kb $hard_limit_kb 0 0 /";
            my $salida = system($comando);
            if ($salida != 0) {
                warn "Error al establecer cuota para usuario $usuario con comando: $comando\n";
            }
    

        }

        # Vaciar la cola
        open(my $fh_clear, '>', $cola) or warn "No se pudo vaciar la cola\n";
        close($fh_clear);
    }

    sleep(10);  # Revisa cada 10 segundos
}
