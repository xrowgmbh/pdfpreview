<?php /* #?ini charset="utf-8"?

[TemplateSettings]
ExtensionAutoloadPath[]=pdfpreview

[Cache]
CacheItems[]=pdfpreview

[Cache_pdfpreview]
# [optional] Name of the cache item, key (custom) is used camel-cased if not set
name=pdfpreview
# [optional] Id of cache item, to be used from command line, key (custom) is used if not set
id=pdfpreview
# [optional] If cache item should be cleared using cluster instead of plain file handler, def: false
isClustered=true
# [optional] tags that will trigger clearing of this item, default: empty array
#tags[]=content
# [optional] If cache uses eZExpiryHandler, then this is the key to get expiry time
#expiryKey=global-custom-cache
# [optional] (bool) If this cache is enabled or not, default: true
enabled=true
# [optional] Path where cache is stored, either directory or file if class/purgeClass
# variables are unset, relative to current cache directory is assumed
path=pdfpreview
# [optional] custom cache clear function "<class>::clearCache()" to be called
#class=eZSomeClassName
# [optional] custom cache purge function "<class>::purgeCache()" to be called
#purgeClass=eZSomeClassName

*/ ?>