<?php

function base62_encode(string $hash, int $base = 16): string {
  return gmp_strval(gmp_init($hash, $base), 62);
}

function base62_decode(string $input, int $base = 16): string {
  return gmp_strval(gmp_init($input, 62), $base);
}
