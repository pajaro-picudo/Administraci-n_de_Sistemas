#!/usr/bin/perl

use strict;
use warnings;
use POSIX qw(strftime);
use MIME::Lite;
use Sys::Hostname;

# Configuración de envío de correo con msmtp
MIME::Lite->send('sendmail', "/usr/bin/msmtp -t");

# CONFIGURACIÓN
my $auth_log = '/var/log/auth.log';
my $registro_alertas = '/var/log/monitor_accesos/ssh_access.log';
my $posicion_lectura = '/var/log/monitor_accesos/auth_log.pos';
my $informe_sistema = '/var/log/monitor_accesos/informe_sistema.log';

my $admin_email = 'id00825497@usal.es';
my $origen_email = 'alorenzojerez@gmail.com';

# Aseguramos que el directorio existe
system("mkdir -p /var/log/monitor_accesos");

# Leemos posición previa (si existe)
my $last_pos = 0;
if (-e $posicion_lectura) {
    open(my $pos_fh_in, '<', $posicion_lectura) or die "No se puede abrir $posicion_lectura: $!";
    chomp($last_pos = <$pos_fh_in>);
    close($pos_fh_in);
}

# Leemos el log desde la última posición
open(my $auth_fh, '<', $auth_log) or die "No puedo abrir $auth_log: $!";
seek($auth_fh, $last_pos, 0);
my @nuevas_lineas = <$auth_fh>;
my $new_pos = tell($auth_fh);
close($auth_fh);

# Guardamos nueva posición
open(my $pos_fh_out, '>', $posicion_lectura) or die "No se puede abrir $posicion_lectura para escritura: $!";
print $pos_fh_out "$new_pos\n";
close($pos_fh_out);

# Procesamos nuevas líneas de acceso SSH
foreach my $line (@nuevas_lineas) {
    chomp($line);

    if ($line =~ /Accepted (?:password|publickey) for (\w+) from ([\d\.]+) port (\d+)/) {
        my ($user, $ip, $port) = ($1, $2, $3);
        registrar_alerta($line);
        enviar_alerta($user, $ip, $line);
    }
}

# Registrar accesos
sub registrar_alerta {
    my $entrada = shift;
    open(my $log_alert, '>>', $registro_alertas) or die "No puedo abrir $registro_alertas: $!";
    print $log_alert strftime("%Y-%m-%d %H:%M:%S", localtime) . " - $entrada\n";
    close($log_alert);
}

# Enviar alerta individual de acceso
sub enviar_alerta {
    my ($user, $ip, $mensaje) = @_;

    my $msg = MIME::Lite->new(
        From    => $origen_email,
        To      => $admin_email,
        Subject => "Alerta SSH: acceso de $user",
        Data    => "Acceso SSH detectado:\n\nUsuario: $user\nIP: $ip\nHora: " . strftime("%Y-%m-%d %H:%M:%S", localtime) . "\n\nMensaje original:\n$mensaje"
    );

    $msg->send;
}

### Generamos el informe de estado completo ###

# Recolectamos datos del sistema
my $fecha = strftime("%Y-%m-%d %H:%M:%S", localtime);
my $hostname = hostname();
my $cpu_load = `uptime`;
my $memoria = `free -h`;
my $disco = `df -h`;
my $usuarios = `who`;
my $procesos = `ps -eo pid,comm,%cpu,%mem --sort=-%cpu | head -10`;

# Creamos el informe
open(my $inf, '>', $informe_sistema) or die "No puedo abrir $informe_sistema: $!";
print $inf <<"FIN";
===============================
INFORME DIARIO DE RECURSOS
Servidor: $hostname
Fecha: $fecha
===============================

---- CARGA DE CPU ----
$cpu_load

---- MEMORIA ----
$memoria

---- DISCO ----
$disco

---- USUARIOS CONECTADOS ----
$usuarios

---- TOP 10 PROCESOS ----
$procesos

---- ACCESOS SSH RECIENTES ----
FIN

# Incluimos accesos recientes en el informe
my $ultimos = `tail -20 $registro_alertas`;
print $inf "$ultimos\n";

close($inf);

# Enviamos el informe diario completo por correo
my $msg2 = MIME::Lite->new(
    From    => $origen_email,
    To      => $admin_email,
    Subject => "Informe Diario del Servidor $hostname",
    Type    => 'multipart/mixed'
);

$msg2->attach(
    Type => 'TEXT',
    Data => "Adjunto se envía el informe diario del estado de recursos del servidor."
);

$msg2->attach(
    Type => 'TEXT',
    Path => $informe_sistema,
    Filename => "informe_sistema.log",
    Disposition => 'attachment'
);

$msg2->send;
