FROM php:8.2-cli

# Instala ZIP por si lo necesitas
RUN apt-get update && apt-get install -y unzip libzip-dev && docker-php-ext-install zip

# Copia tu proyecto al servidor
COPY . /var/www/html

# Cambia el directorio de trabajo
WORKDIR /var/www/html

# Ejecuta PHP y usa "inicio.php" como punto de entrada
CMD ["php", "-S", "0.0.0.0:10000", "inicio.php"]
