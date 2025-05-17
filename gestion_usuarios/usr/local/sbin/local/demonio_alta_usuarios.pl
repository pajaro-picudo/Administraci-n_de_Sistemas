#!/usr/bin/perl

use strict;
use warnings;
use JSON;
use File::Copy qw(copy);
use POSIX qw(getpwnam getgrnam);

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
            next unless $solicitud;

            my $usuario    = $solicitud->{usuario};
            my $nombre     = $solicitud->{nombre};
            my $contrasena = $solicitud->{contrasena};
            my $grupo      = $solicitud->{grupo} || 'usuarios';

            system("useradd -m -s /bin/bash -g $grupo $usuario");
            open(my $pw, '|-', 'chpasswd') or die "No se pudo abrir chpasswd: $!";
            print $pw "$usuario:$contrasena\n";
            close($pw);

            copy($instrucciones_txt, "/home/$usuario/instrucciones.txt");

            mkdir("$base_web/$usuario", 0755);
            copy($plantilla_html, "$base_web/$usuario/index.html");

            my $uid = getpwnam($usuario);
            my $gid = getgrnam($grupo);
            chown($uid, $gid, "$base_web/$usuario/index.html");

            system("setquota -u $usuario 100000 150000 0 0 -a /");
        }

        # Limpia la cola
        open(my $fh_clear, '>', $cola);
        close($fh_clear);
    }

    sleep(10);  # Esperar 10 segundos antes de revisar otra vez
}
