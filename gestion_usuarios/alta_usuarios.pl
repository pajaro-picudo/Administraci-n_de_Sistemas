#!/usr/bin/perl

use strict;
use warnings;
use Linux::usermod;
use Quota;
use DBI;
use Crypt::PasswdMD5;
use File::Path qw(make_path);
use File::Copy;

# Configuración
my $usuario    = shift or die "Debes proporcionar un nombre de usuario.\n";
my $passwd     = shift or die "Debes proporcionar una contraseña.\n";
my $grupo      = 'usuariosbio';
my $cuota_bloques = 100000;  # 100MB en bloques (1 bloque = 1KB)
my $cuota_inodos  = 1000;

# 1. Crear el grupo si no existe
system("getent group $grupo > /dev/null || groupadd $grupo") == 0 or die "Error al crear grupo.\n";

# 2. Crear usuario
my $user = Linux::usermod->new($usuario);
if ($user->exists) {
    die "El usuario ya existe.\n";
}

$user->add(
    password => unix_md5_crypt($passwd, $usuario),
    group    => $grupo,
    home     => "/home/$usuario",
    shell    => '/bin/bash'
);

# 3. Crear estructura de directorios
make_path("/home/$usuario/public_html") or die "No se pudo crear home.\n";

# 4. Crear archivo instrucciones.txt
open my $fh, '>', "/home/$usuario/instrucciones.txt" or die "Error creando instrucciones.\n";
print $fh "Normas de uso del sistema:\n- No compartir la cuenta\n- No usar más recursos de los asignados\n";
close $fh;

# 5. Crear index.html
open my $html, '>', "/home/$usuario/public_html/index.html" or die "Error creando index.html\n";
print $html "<html><body><h1>Blog en Construcción</h1><img src='logo.png'></body></html>\n";
close $html;

# 6. Establecer permisos correctos
system("chown -R $usuario:$grupo /home/$usuario") == 0 or die "Error asignando permisos.\n";

# 7. Establecer cuotas
my ($uid) = getpwnam($usuario);
Quota::setqlim('/', $uid, $cuota_bloques, $cuota_bloques, $cuota_inodos, $cuota_inodos, 0) 
    or warn "No se pudo establecer la cuota.\n";

# 8. Insertar en base de datos
my $dbh = DBI->connect("DBI:mysql:database=bioinformatica;host=localhost", "usuario", "contraseña", { RaiseError => 1 });
my $sth = $dbh->prepare("INSERT INTO usuarios (username, password, tipo, fecha_registro) VALUES (?, ?, 'estudiante', NOW())");
$sth->execute($usuario, $passwd);
$sth->finish;
$dbh->disconnect;

print "✅ Usuario $usuario creado correctamente.\n";
