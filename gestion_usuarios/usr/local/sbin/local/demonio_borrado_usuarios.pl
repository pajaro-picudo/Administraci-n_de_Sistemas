#!/usr/local/sbin/

use strict;
use warnings;
use JSON;
use File::Path qw(remove_tree);
use DBI;

# Configuración
my $cola = '/var/cola_usuarios/baja_usuarios.queue';
my $web_base = '/var/www/bioinformatica/public_html/usuarios';
my $dsn = "DBI:mysql:database=bioinformatica;host=localhost";
my $db_user = "admin_usal";
my $db_pass = "458907";

# Bucle del demonio
while (1) {
    if (-s $cola) {
        open(my $fh, '<', $cola) or die "No se pudo abrir la cola: $!";
        my @lineas = <$fh>;
        close($fh);

        # Limpiar la cola antes de procesar
        open(my $clear, '>', $cola) or die "No se pudo vaciar la cola: $!";
        close($clear);

        # Conectar a la base de datos
        my $dbh = DBI->connect($dsn, $db_user, $db_pass, { RaiseError => 1, PrintError => 0, mysql_enable_utf8 => 1 });

        foreach my $linea (@lineas) {
            chomp $linea;
            my $datos = eval { decode_json($linea) };
            next unless $datos && $datos->{usuario};

            my $usuario = $datos->{usuario};
            print "Procesando baja para el usuario: $usuario\n";

            # 1. Eliminar de la base de datos
            eval {
                my $sth = $dbh->prepare("DELETE FROM usuarios WHERE username = ?");
                $sth->execute($usuario);
                print "✔ Usuario $usuario eliminado de la base de datos.\n";
            };

            # 2. Eliminar carpeta web
            my $web_path = "$web_base/$usuario";
            if (-d $web_path) {
                remove_tree($web_path, { error => \my $err });
                if (@$err) {
                    warn "⚠ Error al eliminar $web_path: @$err\n";
                } else {
                    print "✔ Carpeta web $web_path eliminada.\n";
                }
            }

            # 3. Eliminar carpeta personal en /home
            my $home_path = "/home/$usuario";
            if (-d $home_path) {
                remove_tree($home_path, { error => \my $err2 });
                if (@$err2) {
                    warn "⚠ Error al eliminar $home_path: @$err2\n";
                } else {
                    print "✔ Carpeta personal $home_path eliminada.\n";
                }
            }

            # 4. Eliminar el usuario del sistema
            my $status = system("userdel", $usuario);
            if ($status == 0) {
                print "✔ Usuario $usuario eliminado del sistema.\n";
            } else {
                warn "⚠ No se pudo eliminar el usuario $usuario (¿ya no existe?).\n";
            }
        }

        $dbh->disconnect;
    }

    sleep(10);  # Espera entre ciclos
}
