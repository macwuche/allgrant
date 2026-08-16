{ pkgs }: {
  deps = [
    pkgs.php81
    pkgs.php81Packages.composer
    pkgs.php81Extensions.pdo
    pkgs.php81Extensions.pdo_pgsql
    pkgs.php81Extensions.pgsql
    pkgs.php81Extensions.mbstring
    pkgs.php81Extensions.openssl
    pkgs.php81Extensions.tokenizer
    pkgs.php81Extensions.xml
    pkgs.php81Extensions.ctype
    pkgs.php81Extensions.json
    pkgs.php81Extensions.bcmath
    pkgs.php81Extensions.fileinfo
    pkgs.php81Extensions.gd
    pkgs.php81Extensions.curl
    pkgs.php81Extensions.zip
    pkgs.php81Extensions.intl
  ];
}
