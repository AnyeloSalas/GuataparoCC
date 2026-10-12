<?php
$t1 = "MzAxMA3VFUhgcBkRFTho5R3lJmiogCcBFThoYitVJUhg5TNtJliIfzXVFViAcj2lFBiA2UKBFjh4C0qlJziYj2VNJmiYD299JEiAVHvRJfiIaoVNJXiQ140JJUiQTJptJBiAyJvRJjhwIJxxFzh4IJ1lFoh4i6a5FEhgTKXJFihol67pJBhow66dJliQJLEpJsiaYbXxJjhoIrYxJyhombtdJpiafL9FFjiTSNItJqiYOdfFFViIRdmxFpiQPNsRF1igLN79JXh4PeBZFkiQsuCxFyioJePBJFiQTurNFjiJOPGBFsiZvvVZJGiow/c9J6iQnfvVJsiajAEyJUh4TgESF+ighwWqF5iIlgu6JGiQxgseJriIyQqeJuiI7QzmFhihfAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAle4zYBMAjTQhQ5oMahgEA0DNEou8dgMR6RdhAR9C0r4KD85dMVSJDddicAAAMxYkp2wg4NoafBwPAFLiECDvDAES3htIAA4QEboUl3w5IVRwDkyYGgChzRw5PUgiYBYYIpwhJGCeHUUvHEEyyhXDdlMys8sIC9JVEyC9ID0BbREighmZ4jQFEVQTTAAAAAAAUqU=";

$t2 = "MzAxNxt1FThw5S25JVh06Ds5FliUdm7RJDh0VSVJJ1jEgkvVFkh4Dm+ZJniQEIQhJsiQcJ5VFhiFE4qZJYiM25FRJTiIUpe5JChwy6X5FCh0ULBlFDh8w68hJSh8yaA5FmiRZ6mpFoiEi6gRJzh0IuTlJEiIULvRJliMJtiJJgieOam1F1iMIMZtJziEm+I9J0iwL+TNFpigPd/pFUiASeghJoh4Q/CJJtiMs/fpFliVOP2RJqiZwANmJkiNxQ9GJuiMzwVGJUiEUg1SJhiJ0AfuJriSjQVaJ9icoRAqF/iUje+FJBiUxjwBFShs22JpJThw2TvRJjiAhCdlFUhwYzapFjiEEd1VJuiAPtRZJ0iknQrSJDiMybxdJkimZxWuJuiY7RBVJjiQdhHJJFiQ3PW9JziQLxj2FtiQfCB2JEigzXjdF2igkoDVJziQGQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAle4zXhMA9hkRwb4PZ69cEDFhCBONGCExuQdwThkxUsEJ268HADDOEIu1BgGxuS5nAR9C0r4GEHwRIVMQClJ8IwHQmxMmERsAAcMrPuo0AnFUDFBTVjEAvBpDYRoAIaYYPXZRArPOBQ5SPQAQYRuzrA4hYTQCdg5pEjGCFps8JRdyghUgGkgQYugqEQAAAAAAyrU=";

echo "--- ANÁLISIS DE SIMILITUD ---\n";

// 1. similar_text Base64
$p = 0;
similar_text($t1, $t2, $p);
echo "1. similar_text (Base64): " . number_format($p, 2) . "%\n";

// 2. Levenshtein Base64
$lev = levenshtein($t1, $t2);
$len = max(strlen($t1), strlen($t2));
$pLev = (1 - $lev / $len) * 100;
echo "2. Levenshtein (Base64): " . number_format($pLev, 2) . "%\n";

// 3. Binary Comparison
$b1 = base64_decode($t1);
$b2 = base64_decode($t2);

echo "Longitudes Binarias: " . strlen($b1) . " vs " . strlen($b2) . "\n";

// 3.1 similar_text Binario (convertido a hex para function safe)
$h1 = bin2hex($b1);
$h2 = bin2hex($b2);
$p = 0;
similar_text($h1, $h2, $p);
echo "3. similar_text (Hex): " . number_format($p, 2) . "%\n";

// 4. Header Analysis (First 4 bytes)
echo "Header T1: " . bin2hex(substr($b1, 0, 4)) . "\n";
echo "Header T2: " . bin2hex(substr($b2, 0, 4)) . "\n";

// 5. Byte match count (positional)
$matches = 0;
for ($i = 0; $i < min(strlen($b1), strlen($b2)); $i++) {
    if ($b1[$i] === $b2[$i]) $matches++;
}
$pMatch = ($matches / strlen($b1)) * 100;
echo "5. Positional Byte Match: " . number_format($pMatch, 2) . "%\n";

?>
