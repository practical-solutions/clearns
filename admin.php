<?php
/**
 * Clear a Namespace
 * 
 * Admin Plugin erasing page and media directories and creating cronjobs to do
 * this automatically
 * 
 * @license    GPL2
 * @author     Gero Gothe <gero.gothe@medizindoku.de>
 * 
 */
 
class admin_plugin_clearns extends DokuWiki_Admin_Plugin {
    

    function getMenuText($language){
        return "Erase Namespace";
    }

    
    function forAdminOnly(){
        return true;
    }


    function handle() {
        
        # Erase pages of a namespace
        if (isset($_REQUEST['ERASE:DATA']) && $_REQUEST['confirm'] == 'Confirm') {
            $this->rrmdir($_REQUEST['ERASE:DATA'],$_REQUEST['excludes']);     
        }
        
        # Erase medias of a namespace
        if (isset($_REQUEST['ERASE:MEDIA']) && $_REQUEST['confirm'] == 'Confirm') {
            $this->rrmdir($_REQUEST['ERASE:MEDIA']);        
        }
        
        # Erase a cronjob file
        if ($_REQUEST['order'] == "Erase Cronfile") {
            $res = unlink(DOKU_INC.'/lib/plugins/clearns/cron.php');
            if ($res) {msg("Erased cronjob file",1);} else {msg("Cronjob file could not be deleted",-1);}
        }
        
    }
     
    /**
    * output appropriate html
    */
    function html() {
        global $conf;
        echo '<h1>Erase Namespace</h1>';
        echo 'This plugin directly erases the folders of a namespace in the "pages" and "media" directories. So there will be no entry in the changelog. The oldversions are not erased, thus the files can bes restored anyway.<br><br><hr>';
        
        echo '<form action="'.wl($ID).'" method="post">';
        # output hidden values to ensure dokuwiki will return back to this plugin
        echo '<input type="hidden" name="do"   value="admin" />';
        echo '<input type="hidden" name="page" value="'.$this->getPluginName().'" />';
        
        echo 'Do not erase following pages: <input type="text" name="excludes"> (comma-separated, each surrounded by <code>\'</code>, e.g. <code>\'start\',\'sidebar\'</code><br><br>';    
        echo 'Namespace: <input type="text" name="dir">';        
        echo ' <input type="submit" name="order" value="Execute"> <input type="submit" name="order" value="Cron">';
        
        # Confirm direct deletion of a namespace
        if ($_REQUEST['dir']!="" && $_REQUEST['order'] == "Execute") {
            echo '<br><br><hr>';
            $dir_f = $conf['datadir'].'/'.$_REQUEST['dir']; # pages directory
            $dir_m = $conf['mediadir'].'/'.$_REQUEST['dir']; # media directory
            
            if (file_exists($dir_f) && is_dir($dir_f)) {
                $valid1 = true;
                echo "<input type='hidden' name='ERASE:DATA' value='$dir_f'>";
            } else {$valid1 = false;}
            if (file_exists($dir_m) && is_dir($dir_m)) {
                $valid2 = true;
                echo "<input type='hidden' name='ERASE:MEDIA' value='$dir_m'>";
            } else {$valid2 = false;}
            
            echo '<input type="hidden" name="excludes"   value="'.($_REQUEST['excludes']).'" />';
            
            echo "Check Directories: <br><br><code>$dir_f</code> ...<b>".($valid1? "found":"NOT FOUND")."</b>";
            echo "<br><code>$dir_m</code> ...<b>".($valid2? "found":"NOT FOUND")."</b>";
            
            echo '<br><br>Please confirm action:<input type="submit" name="confirm" value="Confirm">';
        }
        
        echo '<br><br><hr>';
        
        # Create file for cronjob
        if ($_REQUEST['dir']!="" && $_REQUEST['order'] == "Cron") {
            $this->createCron($_REQUEST['dir'],$_REQUEST['excludes']);
        }
        
        # Section showing cronfile details
        if (file_exists(DOKU_INC.'/lib/plugins/clearns/cron.php')) {
            $link = DOKU_URL.'lib/plugins/clearns/cron.php';
            $internal = DOKU_INC.'lib/plugins/clearns/cron.php';
            echo 'File for cronjob found:<br>';
            echo "URL <code>$link</code> <a target='_blank' href='$link'>Test it</a><br>";
            echo "Internal: <code>$internal</code><br>";
            echo '<br><input type="submit" name ="order" value="Erase Cronfile"><br><br><hr>';
        
        } else echo 'Currently no file for Cronjob. You can enter the name of a namespace above and press "Cron". This will create a script which erases the namespace and can be called for a cronjob.';
        
        
        echo '</form>';
        
        # For Debug purposes
        #echo '<pre>';
        #print_r($_REQUEST);
        #echo '</pre>';
    }

    
    /* Recursively erase a directory and its files
     * 
     * Modified version, original source: https://www.php.net/manual/en/function.rmdir.php#98622
     */
    function rrmdir($dir,$excludes='') {
        $dir = "$dir/";
        
        $excludes = explode(',',$excludes);
        foreach ($excludes as &$e) $e.= '.txt';

        if (is_dir($dir)) {
            $objects = scandir($dir);
            
            foreach ($objects as $object) {
                if ($object != "." && $object != ".." && !in_array($object,$excludes)) {
                    if (filetype($dir."/".$object) == "dir") 
                        $this->rrmdir($dir."/".$object); 
                    else unlink   ($dir."/".$object);
                }
            }
            reset($objects);
            rmdir($dir);
            msg("<code>$dir</code> erased",1);
        } else msg("Directory not identified: <code>$dir</code>",-1);
    }
    
    
    /* Creates a file "cron.php" in the plugin directory
     * The execution of this file deletes the directories of the namespace in the "pages/" and "media/"-directories
     * 
     * @param $dir = the namespace
     */
    function createCron($dir,$excludes=''){
        global $conf;
        $base = DOKU_INC.'/lib/plugins/clearns/';
        $content = file_get_contents($base.'cron_tpl.php');
        
        $dirf = $conf['datadir'].'/'.$dir; # pages directory
        $dirm = $conf['mediadir'].'/'.$dir; # media directory
        
        $content = str_replace('%EXCLUDES%',$excludes,$content);
        $content = str_replace('%DIRF%',$dirf,$content);
        $content = str_replace('%DIRM%',$dirm,$content);
        $content = str_replace('exit;',"",$content);
        
        
        file_put_contents($base.'cron.php',$content);
        
        msg ("New Cron-File written.",1);
    }
     
}


