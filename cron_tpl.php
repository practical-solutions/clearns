<?php
exit;

function rrmdir($dir) {
    $dir = "$dir/";

    if (is_dir($dir)) {
        $objects = scandir($dir);
            
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir") 
                    rrmdir($dir."/".$object); 
                else unlink   ($dir."/".$object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

rrmdir("%DIRF%");
rrmdir("%DIRM%");

echo "Erased directories:<br>%DIRF%<br>%DIRM%";

?>
