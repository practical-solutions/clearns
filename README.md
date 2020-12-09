# DokuWiki-Plugin: Clear a Namespace

This plugin provides an admin section where
* namespaces can be erased
* a php script can be created to erase a namespace (intention: to be called as a cronjob)

This plugin directly erases the folders of the namespaces without making an entry to the changelog. The oldrevisions are not erased and thus can be restored in dokuwiki.

## Compatibility

Tested with

* PHP **7.3**
* DokuWiki / **Hogfather**
