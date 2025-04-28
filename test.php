<?php
file_put_contents('writable/logs/test.txt', 'Tes tulis log manual' . PHP_EOL, FILE_APPEND);
echo "Sudah dicoba nulis ke writable/logs/test.txt";
