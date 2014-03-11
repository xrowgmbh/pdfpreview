<code>
/*
    PDF preview for eZ Publish
    Copyright (C) 2009  xrow GbR, Hannover Germany, http://xrow.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/

### BEGIN INIT INFO
# Provides:          pdfpreview
# Depends:		     imagemagick + ghostscript
# OS:			     Linux, FreeBSD, Windows
# Version:		     > eZ 4.x		
# Developed:	     Björn Dieding  ( bjoern [at] xrow [dot] de )
# Short-Description: PDF Preview image generator
# Description:       Generates an image from a single PDF page  
# Resources:	     http://projects.ez.no/pdfpreview
### END INIT INFO
</code>

#### Setup ####

install imagemagick + ghostscript + activate extension

#### Usage ####

pathtofile|pdfpreview( width [ height, [, page [, name ] ] ] )

<code>
{if $node.object.data_map.file.content.mime_type|eq('application/pdf')}
<img src={$node.object.data_map.file.content.filepath|pdfpreview( 88, 88, 1, "My PDF.pdf" )|ezroot} alt="Preview">
{/if}
</code>

#### Troubleshooting ####

Look in the debug output

For further information contract service [at] xrow [dot] de