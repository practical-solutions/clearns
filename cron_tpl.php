<?php
exit;

function rrmdir($dir,$excludes='') {
    $excludes = explode(',',$excludes);
    foreach ($excludes as &$e) $e.= '.txt';

    $dir = "$dir/";

    if (is_dir($dir)) {
        $objects = scandir($dir);
            
        foreach ($objects as $object) {
            if ($object != "." && $object != ".." && !in_array($object,$excludes)) {
                if (filetype($dir."/".$object) == "dir") 
                    rrmdir($dir."/".$object); 
                else unlink   ($dir."/".$object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

rrmdir("%DIRF%","%EXCLUDES%");
rrmdir("%DIRM%");

echo "Erased directories:<br>%DIRF%<br>%DIRM%";

?>
