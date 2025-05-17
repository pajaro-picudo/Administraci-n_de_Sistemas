#!/usr/bin/perl

use strict;
use warnings;
use JSON;
use File::Copy qw(copy);
use POSIX qw(getpwnam getgrnam);

# Configuración
my $cola = '/var/cola_usuarios/alta_usuarios.queue';
my $base_web = '/var/www/bioinformatica/public_html/usuarios';
my $instrucciones_txt = '/etc/skel/instrucciones.txt';
my $plantilla_html = '/etc/skel/index.html';

open(my $fh, "<", $cola) or die "No se pudo abrir la cola: $!";
while (my $linea = <$fh>) {
    chomp $linea;
    my $solicitud = eval { decode_json($linea) };
    next unless $solicitud;

    my $usuario    = $solicitud->{usuario};
    my $nombre     = $solicitud->{nombre};
    my $contrasena = $solicitud->{contrasena};
    my $grupo      = $solicitud->{grupo} || 'usuarios';

    # Verificar si el grupo existe
    my $gid = getgrnam($grupo);
    if (!defined $gid) {
        warn "Grupo '$grupo' no existe. Skipping user $usuario\n";
        next;
    }

    # Crear usuario
    system("useradd -m -s /bin/bash -g $grupo $usuario") == 0
        or warn "Error al crear usuario $usuario\n";

    # Asignar contraseña
    open(my $pw, "|-", "chpasswd") or die "No se pudo abrir chpasswd: $!";
    print $pw "$usuario:$contrasena\n";
    close($pw);

    # Crear estructura web
    mkdir("$base_web/$usuario", 0755);
    copy($plantilla_html, "$base_web/$usuario/index.html");
    copy($instrucciones_txt, "/home/$usuario/instrucciones.txt");

    my $uid = getpwnam($usuario);
    chown($uid, $gid, "$base_web/$usuario/index.html");

    # Asignar cuota (opcional, ignora errores)
    system("setquota -u $usuario 100000 150000 0 0 -a /");

    print "Usuario $usuario creado correctamente.\n";
}
close($fh);

# Limpiar la cola
truncate($cola, 0);
