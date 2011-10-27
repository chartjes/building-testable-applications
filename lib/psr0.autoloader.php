<?php

// autoloader support
function psr0_autoloader_normalise_path($className)
{
    $fileName  = '';
    $lastNsPos = strripos($className, '\\');

    if ($lastNsPos !== false)
    {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }

    return $fileName . str_replace('_', DIRECTORY_SEPARATOR, $className);

}

function psr0_autoloader_init_namespace($namespace, $fileExt = '.init.php')
{
    static $loadedNamespaces = array();

    // have we loaded this namespace before?
    if (isset($loadedNamespaces[$namespace]))
    {
        // yes we have - bail
        return;
    }

    $path = psr0_autoloader_normalise_path($namespace);
    $filename = $path . '/_init/' . end(explode('/', $path)) . $fileExt;
    $loadedModules[$namespace] = $filename;

    psr0_autoloader_include($filename);
}

function psr0_autoloader_autoload($classname)
{
    if (class_exists($classname) || interface_exists($classname))
    {
        return FALSE;
    }

    // convert the classname into a filename on disk
    $classFile = psr0_autoloader_normalise_path($classname) . '.php';

    return psr0_autoloader_include($classFile);
}

function psr0_autoloader_include($filename)
{
    $pathToSearch = explode(PATH_SEPARATOR, get_include_path());

    // keep track of what we have tried; this info may help other
    // devs debug their code
    $failedFiles = array();

    foreach ($pathToSearch as $searchPath)
    {
        $fileToLoad = $searchPath . '/' . $filename;
        // var_dump($fileToLoad);
        if (!file_exists($fileToLoad))
        {
            $failedFiles[] = $fileToLoad;
            continue;
        }

        require($fileToLoad);
        return TRUE;
    }

    // if we get here, we could not find the requested file
    // we do not die() or throw an exception, because there may
    // be other autoload functions also registered
    return FALSE;
}

function psr0_autoloader_searchFirst($dir)
{
    $dir = realpath($dir);

    psr0_autoloader_dontSearchIn($dir);

    // add the new directory to the front of the path
    set_include_path($dir . PATH_SEPARATOR . get_include_path());
}

function psr0_autoloader_searchLast($dir)
{
    $dir = realpath($dir);

    psr0_autoloader_dontSearchIn($dir);

    // add the new directory to the end of the path
    set_include_path(get_include_path() . PATH_SEPARATOR . $dir);
}

function psr0_autoloader_dontSearchIn($dir)
{
    // check to make sure that $dir is not already in the path
    $pathToSearch = explode(PATH_SEPARATOR, get_include_path());

    foreach ($pathToSearch as $dirToSearch)
    {
        $dirToSearch = realpath($dirToSearch);
        if ($dirToSearch == $dir)
        {
            // we have found it
            // remove it from the list
            // $key points to the *next* entry in the list,
            // not the current entry
            $key = key($pathToSearch);
            $key -= 1;
            unset($pathToSearch[$key]);
        }
    }

    // set the revised search path
    set_include_path(implode(PATH_SEPARATOR, $pathToSearch));
}

spl_autoload_register('psr0_autoloader_autoload');
// assume that we are at the top of a vendor tree to load from
psr0_autoloader_searchFirst(LIB_ROOT);

?>
