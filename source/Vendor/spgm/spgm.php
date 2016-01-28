<?php

#  SPGM (Simple Picture Gallery Manager), a basic and configurable PHP script
#  to display picture galleries on the web
#  Copyright 2002-2007, Sylvain Pajot <spajot@users.sourceforge.net>
#  Official website: http://spgm.sourceforge.net
#
#  This program is free software; you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation; either version 2 of the License, or
#  (at your option) any later version.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	 See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program; if not, write to the Free Software
#  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
###### Toggles #############
define('MODE_TRACE', false); // toggles debug mode
define('MODE_WARNING', true); // toggles warning mode
define('DIR_GAL', 'screenshots/'); // galleries base directory (relative path from
// spgm.php or the file requiring it if there's one)
define('BASE_DIR', $base_dir); // galleries base directory (relative path from
// spgm.php or the file requiring it if there's one)
define('DIR_LANG', 'source/Vendor/spgm/lang/'); // language packs (relative path from spgm.php or
// the file requiring it if there's one)
define('DIR_THEMES', 'source/Vendor/spgm/flavors/'); // themes base directory (relative path from
// spgm.php or the file requiring it
// if there's one)
define('DIR_THUMBS', 'thumbs/'); // if defined, points to the directory
// where thumbnails reside, relatively
// from the gallery directory

define('FILE_GAL_TITLE', 'gal-title.txt'); // default title file for a gallery
define('FILE_GAL_SORT', 'gal-sort.txt'); // file for explicit gallery sort
define('FILE_GAL_CAPTION', 'gal-desc.txt'); // default caption file for a gallery
define('FILE_GAL_HIDE', 'gal-hide.txt'); // default file to enable gallery hide
define('FILE_PIC_SORT', 'pic-sort.txt'); // file for explicit picture sort
define('FILE_PIC_CAPTIONS', 'pic-desc.txt'); // default caption file for pictures/thumbnails
define('FILE_THEME', 'spgm.thm'); // theme file
define('FILE_CONF', 'spgm.conf'); // config file
define('FILE_LANG', 'lang'); // language file short name (without extension)
define('PREF_THUMB', ''); // prefix for thumbnail pictures
// MUST NOT be empty if DIR_THUMBS is used
define('EXT_PIC_CAPTION', '.cmt'); // file extension for pictures comment (DEPRECATED)
define('CAPTION_DELIMITER', '|');
define('CAPTION_KEEPER', '>');

define('PARAM_PREFIX', 'spgm'); // MUST NOT be empty
define('PARAM_NAME_GALID', PARAM_PREFIX . 'Gal');
define('PARAM_NAME_PICID', PARAM_PREFIX . 'Pic');
define('PARAM_NAME_PAGE', PARAM_PREFIX . 'Page');
define('PARAM_NAME_FILTER', PARAM_PREFIX . 'Filters');
define('PARAM_VALUE_FILTER_NEW', 'n');
define('PARAM_VALUE_FILTER_NOTHUMBS', 't');
define('PARAM_VALUE_FILTER_SLIDESHOW', 's');

define('CLASS_TABLE_WRAPPER', 'table-wrapper');
define('CLASS_TABLE_MAIN_TITLE', 'table-main-title');
define('CLASS_TD_SPGM_LINK', 'td-main-title-spgm-link');
define('CLASS_A_SPGM_LINK', 'a-spgm-link');
define('CLASS_TABLE_GALLISTING_GRID', 'table-gallisting-grid');
define('CLASS_TD_GALLISTING_CELL', 'td-gallisting-cell');
define('CLASS_TABLE_GALITEM', 'table-galitem');
define('CLASS_TD_GALITEM_ICON', 'td-galitem-icon');
define('CLASS_TD_GALITEM_TITLE', 'td-galitem-title');
define('CLASS_TD_GALITEM_CAPTION', 'td-galitem-caption');
define('CLASS_TABLE_PICTURE', 'table-picture');
define('CLASS_TD_PICTURE_NAVI', 'td-picture-navi');
define('CLASS_TD_ZOOM_FACTORS', 'td-zoom-factors');
define('ID_PICTURE', 'picture');
define('ID_PICTURE_CAPTION', 'picture-caption');
define('CLASS_BUTTON_ZOOM_FACTORS', 'button-zoom-factors');
define('CLASS_TD_PICTURE_PIC', 'td-picture-pic');
define('ID_PICTURE_NAVI', 'pic-navi');
define('CLASS_TD_PICTURE_FILENAME', 'td-picture-filename');
define('CLASS_TD_PICTURE_CAPTION', 'td-picture-caption');
define('CLASS_TABLE_THUMBNAILS', 'table-thumbnails');
define('CLASS_TD_THUMBNAILS_THUMB', 'td-thumbnails-thumb');
define('CLASS_TD_THUMBNAILS_THUMB_SELECTED', 'td-thumbnails-thumb-selected');
define('CLASS_TD_THUMBNAILS_NAVI', 'td-thumbnails-navi');
define('CLASS_DIV_THUMBNAILS_CAPTION', 'div-thumbnails-caption');
define('CLASS_TABLE_SHADOWS', 'table-shadows');
define('CLASS_TD_SHADOWS_RIGHT', 'td-shadows-right');
define('CLASS_TD_SHADOWS_BOTTOM', 'td-shadows-bottom');
define('CLASS_TD_SHADOWS_BOTTOMRIGHT', 'td-shadows-bottomright');
define('CLASS_TD_SHADOWS_MAIN', 'td-shadows-main');
define('CLASS_TABLE_ORIENTATION', 'table-orientation');
define('CLASS_TD_ORIENTATION_LEFT', 'td-orientation-left');
define('CLASS_TD_ORIENTATION_RIGHT', 'td-orientation-right');
define('CLASS_SPAN_FILTERS', 'span-filters');
define('CLASS_IMG_PICTURE', 'img-picture');
define('CLASS_IMG_THUMBNAIL', 'img-thumbnail');
define('CLASS_IMG_THUMBNAIL_SELECTED', 'img-thumbnail-selected');
define('CLASS_IMG_FOLDER', 'img-folder');
define('CLASS_IMG_GALICON', 'img-galicon');
define('CLASS_IMG_PICTURE_PREV', 'img-picture-prev');
define('CLASS_IMG_PICTURE_NEXT', 'img-picture-next');
define('CLASS_IMG_THMBNAVI_PREV', 'img-thmbnavi-prev');
define('CLASS_IMG_THMBNAVI_NEXT', 'img-thmbnavi-next');
define('CLASS_IMG_NEW', 'img-new');
define('CLASS_DIV_GALHEADER', 'div-galheader');

define('ANCHOR_PICTURE', 'spgmPicture');
define('ANCHOR_SPGM', 'spgm');

define('ERRMSG_UNKNOWN_GALLERY', 'unknown gallery');
define('ERRMSG_UNKNOWN_PICTURE', 'unknown picture');
define('ERRMSG_INVALID_NUMBER_OF_PICTURES', 'invalid number of picture');
define('ERRMSG_INVALID_VALUE', 'invalid value');
define('WARNMSG_FILE_INSUFFICIENT_PERMISSIONS', 'insufficient permissions (644 required)');
define('WARNMSG_THUMBNAIL_UNREADABLE', 'no associated thumbnail or insufficient permissions');
define('WARNMSG_DIR_INSUFFICIENT_PERMISSIONS', 'insufficient permissions (755 required)');

define('GALICON_NONE', 0);
define('GALICON_RANDOM', 1);
define('ORIGINAL_SIZE', 0);
define('ORIENTATION_TOPBOTTOM', 0);
define('ORIENTATION_LEFTRIGHT', 1);
define('SORTTYPE_CREATION_DATE', 0);
define('SORTTYPE_NAME', 1);
define('SORT_ASCENDING', 0);
define('SORT_DESCENDING', 1);
define('RIGHT', 0);
define('BOTTOM', 1);

/* multi-language support... */
define('PATTERN_SPGM_LINK', '>SPGM_LINK<');
define('PATTERN_CURRENT_PAGE', '>CURRENT_PAGE<');
define('PATTERN_NB_PAGES', '>NB_PAGES<');
define('PATTERN_CURRENT_PIC', '>CURRENT_PIC<');
define('PATTERN_NB_PICS', '>NB_PICS<');

// Used for variable variables in main function
$strVarGalleryId   = PARAM_NAME_GALID;
$strVarPictureId   = PARAM_NAME_PICID;
$strVarPageIndex   = PARAM_NAME_PAGE;
$strVarFilterFlags = PARAM_NAME_FILTER;

global $spgm_cfg;
$spgm_cfg                                       = array();
$spgm_cfg['conf']['newStatusDuration']          = 120; //minutes
$spgm_cfg['conf']['thumbnailsPerPage']          = 12;
$spgm_cfg['conf']['thumbnailsPerRow']           = 3;
$spgm_cfg['conf']['galleryListingCols']         = 3;
$spgm_cfg['conf']['galleryCaptionPos']          = RIGHT;
$spgm_cfg['conf']['subGalleryLevel']            = 1;
$spgm_cfg['conf']['galleryOrientation']         = ORIENTATION_TOPBOTTOM;
$spgm_cfg['conf']['gallerySortType']            = SORTTYPE_CREATION_DATE;
$spgm_cfg['conf']['gallerySortOptions']         = SORT_DESCENDING;
$spgm_cfg['conf']['pictureSortType']            = SORTTYPE_CREATION_DATE;
$spgm_cfg['conf']['pictureSortOptions']         = SORT_DESCENDING;
$spgm_cfg['conf']['pictureInfoedThumbnails']    = true;
$spgm_cfg['conf']['captionedThumbnails']        = false;
$spgm_cfg['conf']['pictureCaptionedThumbnails'] = false;
$spgm_cfg['conf']['filenameWithThumbnails']     = false;
$spgm_cfg['conf']['filenameWithPictures']       = true;
$spgm_cfg['conf']['enableSlideshow']            = false;
$spgm_cfg['conf']['enableDropShadows']          = false;
$spgm_cfg['conf']['fullPictureWidth']           = 820;
$spgm_cfg['conf']['fullPictureHeight']          = 461;
$spgm_cfg['conf']['popupOverFullPictures']      = true;
$spgm_cfg['conf']['popupPictures']              = false;
$spgm_cfg['conf']['popupFitPicture']            = false;
$spgm_cfg['conf']['popupWidth']                 = 1920;
$spgm_cfg['conf']['popupHeight']                = 1080;
$spgm_cfg['conf']['filters']                    = '';
$spgm_cfg['conf']['exifInfo']                   = array();
$spgm_cfg['conf']['zoomFactors']                = array();
$spgm_cfg['conf']['galleryIconType']            = GALICON_NONE;
$spgm_cfg['conf']['galleryIconHeight']          = ORIGINAL_SIZE;
$spgm_cfg['conf']['galleryIconWidth']           = ORIGINAL_SIZE;
$spgm_cfg['conf']['stickySpgm']                 = false;
$spgm_cfg['conf']['theme']                      = 'default';
$spgm_cfg['conf']['language']                   = 'en';

$spgm_cfg['locale']['spgmLink']         = 'a gallery generated by ' . PATTERN_SPGM_LINK;
$spgm_cfg['locale']['thumbnailNaviBar'] = 'Page ' . PATTERN_CURRENT_PAGE . ' of ' . PATTERN_NB_PAGES;
$spgm_cfg['locale']['filter']           = 'filter';
$spgm_cfg['locale']['filterNew']        = 'new';
$spgm_cfg['locale']['filterAll']        = 'all';
$spgm_cfg['locale']['filterSlideshow']  = 'Slideshow';
$spgm_cfg['locale']['pictureNaviBar']   = 'Picture ' . PATTERN_CURRENT_PIC . ' of ' . PATTERN_NB_PICS;
$spgm_cfg['locale']['newPictures']      = 'new pictures';
$spgm_cfg['locale']['newPicture']       = 'new picture';
$spgm_cfg['locale']['newGallery']       = 'new gallery';
$spgm_cfg['locale']['pictures']         = 'pictures';
$spgm_cfg['locale']['picture']          = 'picture';
$spgm_cfg['locale']['rootGallery']      = 'Main gallery';
$spgm_cfg['locale']['exifHeading']      = 'EXIF data for';

$spgm_cfg['theme']['gallerySmallIcon']    = '';
$spgm_cfg['theme']['galleryBigIcon']      = '';
$spgm_cfg['theme']['newItemIcon']         = '';
$spgm_cfg['theme']['previousPictureIcon'] = '';
$spgm_cfg['theme']['nextPictureIcon']     = '';
$spgm_cfg['theme']['previousPageIcon']    = '&laquo;';
$spgm_cfg['theme']['previousPageIconNot'] = '&laquo;';
$spgm_cfg['theme']['nextPageIcon']        = '&raquo;';
$spgm_cfg['theme']['nextPageIconNot']     = '&raquo;';
$spgm_cfg['theme']['firstPageIcon']       = '&laquo;&laquo;';
$spgm_cfg['theme']['firstPageIconNot']    = '&laquo;&laquo;';
$spgm_cfg['theme']['lastPageIcon']        = '&raquo;&raquo;';
$spgm_cfg['theme']['lastPageIconNot']     = '&raquo;&raquo;';

$spgm_cfg['global']['supportedExtensions'] = array(
    '.jpg',
    '.png',
    '.gif'
); // supported picture file extensions
$spgm_cfg['global']['ignoredDirectories']  = array(
    'vti_cnf/',
    '_vti_cnf/'
); // directories to ignore, add some more if needed
if (defined('DIR_THUMBS'))
{
    $spgm_cfg['global']['ignoredDirectories'][] = DIR_THUMBS;
}

$spgm_cfg['global']['propagateFilters'] = false; // used to propagate filters in URLs
$spgm_cfg['global']['documentSelf']     = '';
$spgm_cfg['global']['tmpPathToPics']    = ''; // hack to avoid comparisons of long
// strings (only used by the
// spgm_CallbackCompareMTime
// callback function)
$spgm_cfg['global']['URLExtraParams']   = ''; // Contains the extra paramaters for SPGM
// to be able to link back in template mode




###### REPORTING FUNCTIONS #############################################

function spgm_Error($strErrorMessage)
{
    print '<div style="color: #ff0000; font-family: helvetica, arial; font-size:12pt; font-weight: bold">' . $strErrorMessage . '</div>' . "\n";
}

function spgm_Warning($strWarningMessage)
{
    if (MODE_WARNING)
    {
        print '<div style="color: #0000ff; font-family: helvetica, arial; font-size:12pt; font-weight: bold">' . $strWarningMessage . '</div>' . "\n";
    }
}

function spgm_Trace($strTrace)
{
    if (MODE_TRACE)
    {
        print '<div style="color: #000000; font-family: verdana, helvetica, arial; font-size:12pt">' . $strTrace . '</div>' . "\n";
    }
}



################## DISPLAY FUNCTIONS #####################################

# Builds the A html markup poiting to the URL that is to be built according to the passed parameters
# Parameters description :
# $text : HTML code to click on
# $cssClass : CSS class to apply to the A markup (can be empty)
# $anchor : internal anchor to point to (not generated if empty)
# $galId : gallery to point to (omitted if -1)
# $pageIdx : gallery page to point to (omitted if -1)
# $picId : picture to point to (omitted if -1)
# $filters : filters to set in the URL

function spgm_BuildLink($text, $cssClass, $anchor, $galId, $pageIdx, $picId, $filters)
{
    global $spgm_cfg;

    spgm_Trace('<p>function spgm_BuildLink</p>' . "\n" . 'text: ' . $text . '<br />' . "\n" . 'cssClass: ' . $cssClass . '<br />' . "\n" . 'anchor: ' . $anchor . '<br />' . "\n" . 'galId: ' . $galId . '<br />' . "\n" . 'pageIdx: ' . $pageIdx . '<br />' . "\n" . 'picId: ' . $picId . '<br />' . "\n" . 'filters: ' . $filters . '<br />' . "\n");

    $url = $spgm_cfg['global']['documentSelf'] . '?';
    if ($galId != '')
    {
        $url .= PARAM_NAME_GALID . '=' . urlencode($galId);
    }
    if ($pageIdx != -1)
    {
        $url .= '&amp;' . PARAM_NAME_PAGE . '=' . $pageIdx;
    }
    if ($picId != -1)
    {
        $url .= '&amp;' . PARAM_NAME_PICID . '=' . $picId;
    }
    if ($filters != '')
    {
        $url .= '&amp;' . PARAM_NAME_FILTER . '=' . $filters;
    }
    $url .= $spgm_cfg['global']['URLExtraParams'];
    if ($anchor != '')
    {
        $url .= '#' . $anchor;
    }
    elseif ($spgm_cfg['conf']['stickySpgm'] == true)
    {
        $url .= '#' . ANCHOR_SPGM;
    }

    $url = str_replace("removed", "r", $url);

    if ($cssClass == "td-galitem-title" || $cssClass == "")
    {
        $pjax = 'data-replace="true" data-target=".entries" ';
    }

    $link = '<a ' . $pjax . 'href="' . $url . '" class="' . $cssClass . '">' . $text . '</a>';

    return $link;
}

function spgm_DispSPGMLink()
{
    global $spgm_cfg;

    spgm_Trace('<p>function spgm_DispSPGMLink</p>' . "\n");

    // multi-language support
    $spgm_cfg['locale']['spgmLink'] = str_replace(PATTERN_SPGM_LINK, '<a href="http://spgm.sourceforge.net" class="' . CLASS_A_SPGM_LINK . '">SPGM</a>', $spgm_cfg['locale']['spgmLink']);

    print $spgm_cfg['locale']['spgmLink'];
}

function spgm_DropShadowsBeginWrap($offset = '')
{
    global $spgm_cfg;

    spgm_Trace('<p>function spgm_DropShadowsBeginWrap</p>' . "\n");

    // if drop shadows are enabled, draw the beginning of the table
    if ($spgm_cfg['conf']['enableDropShadows'])
    {
        print $offset . '<table class="' . CLASS_TABLE_SHADOWS . '">' . "\n";
        print $offset . '	 <tr>' . "\n";
        print $offset . '		 <td class="' . CLASS_TD_SHADOWS_MAIN . '">' . "\n";
    }

}

function spgm_DropShadowsEndWrap($offset = '')
{
    global $spgm_cfg;

    spgm_Trace('<p>function spgm_DropShadowsEndWrap</p>' . "\n");

    // if drop shadows are enabled, draw the end of the table
    if ($spgm_cfg['conf']['enableDropShadows'])
    {
        print $offset . '		 </td>' . "\n";
        print $offset . '		 <td class="' . CLASS_TD_SHADOWS_RIGHT . '">&nbsp;</td>' . "\n";
        print $offset . '	 </tr>' . "\n";
        print $offset . '	 <tr>' . "\n";
        print $offset . '		 <td class="' . CLASS_TD_SHADOWS_BOTTOM . '">&nbsp;</td>' . "\n";
        print $offset . '		 <td class="' . CLASS_TD_SHADOWS_BOTTOMRIGHT . '">&nbsp;</td>' . "\n";
        print $offset . '	 </tr>' . "\n";
        print $offset . '</table>' . "\n";
    }
}



################################################################################
# Checks if a file or directory is "new"

function spgm_IsNew($strFilePath)
{
    global $spgm_cfg;

    spgm_Trace('<p>function spgm_IsNew</p>' . "\n" . 'strFilePath: ' . $strFilePath . '<br />' . "\n");

    if (!file_exists($strFilePath) || $spgm_cfg['conf']['newStatusDuration'] == 0)
        return false;
    return (filemtime($strFilePath) > (time() - $spgm_cfg['conf']['newStatusDuration'] * 60));
}

################################################################################
# Checks for permissions on either pictures, galleries, config files, etc...

function spgm_CheckPerms($strFilePath)
{
    spgm_Trace('<p>function spgm_CheckPerms</p>' . "\n" . 'strFilePath: ' . $strFilePath . '<br />' . "\n");

    return is_readable($strFilePath);
}

################################################################################
# Checks if the filname exists, refers to a picture associated to a thumbnail
# and is granted the necessary access rigths

function spgm_IsPicture($strPictureFileName, $strGalleryId)
{
    global $spgm_cfg;

    $strPicturePath   = DIR_GAL . $strGalleryId . '/' . $strPictureFileName;
    $strThumbnailPath = DIR_GAL . $strGalleryId . '/' . PREF_THUMB . $strPictureFileName;
    if (defined('DIR_THUMBS'))
    {
        $strThumbnailPath = DIR_GAL . $strGalleryId . '/' . DIR_THUMBS . PREF_THUMB . $strPictureFileName;
    }

    spgm_Trace('<p>function spgm_IsPicture</p>' . "\n" . 'strPictureFileName: ' . $strPictureFileName . '<br />' . "\n" . 'strGalleryId: ' . $strGalleryId . '<br />' . "\n" . 'strPicturePath: ' . $strPicturePath . '<br />' . "\n" . 'strThumbnailPath: ' . $strThumbnailPath . '<br />' . "\n");

    // check filename patterns
    if (PREF_THUMB != '' AND eregi('^' . PREF_THUMB . '*', $strPictureFileName))
        return false;
    $validated = false;
    $extnb     = count($spgm_cfg['global']['supportedExtensions']);
    for ($i = 0; $i < $extnb; $i++)
    {
        if (eregi($spgm_cfg['global']['supportedExtensions'][$i] . '$', $strPictureFileName))
        {
            $validated = true;
            break;
        }
    }
    if (!$validated)
        return false;

    // does it exist, is it a regular file and does it have the expected permissions ?
    if (!spgm_CheckPerms($strPicturePath))
    {
        return false;
    }

    // an associated thumbnail is required... same job again !
    if (!spgm_CheckPerms($strThumbnailPath))
    {
        spgm_Warning($strPicturePath . ': ' . WARNMSG_THUMBNAIL_UNREADABLE . '<br />');
        return false;
    }

    return true;
}

##############################################################################
# Checks if the directory corresponding the gallery is well-formed, exists
# and is granted the necessary access rights
# $galid can be empty

function spgm_IsGallery($strGalleryId)
{
    global $spgm_cfg;

    $strPathToPictures = DIR_GAL . $strGalleryId;

    spgm_Trace('<p>function spgm_IsGallery</p>' . "\n" . 'strGalleryId: ' . $strGalleryId . '<br />' . "\n" . 'strPathToPictures: ' . $strPathToPictures . '<br />' . "\n");

    // searching for hazardous patterns
    if (ereg('^/', $strGalleryId) || ereg('\.\.', $strGalleryId) || ereg('/$', $strGalleryId))
    {
        return false;
    }


    // does it exist, is it a directory ?
    if (!is_dir($strPathToPictures))
        return false;

    // ... is it part of the ignore list ?
    foreach ($spgm_cfg['global']['ignoredDirectories'] as $key => $value)
    {
        if (basename($strGalleryId) . '/' == $value)
        {
            return false;
        }
    }

    // ... does it have the expected permissions ?
    if (!spgm_CheckPerms($strPathToPictures))
    {
        spgm_Warning($strPathToPictures . ': ' . WARNMSG_FILE_INSUFFICIENT_PERMISSIONS . '<br />');
        return false;
    }

    if ($strGalleryId == "Imgur")
        return false;

    return true;
}


################################################################################
# Loads a flavor

function spgm_LoadFlavor($strThemeName)
{
    global $spgm_cfg;

    spgm_Trace('<p>function spgm_LoadFlavor</p>' . "\n" . 'strThemeName: ' . $strThemeName . '<br />' . "\n");

    if (spgm_CheckPerms(DIR_THEMES . $strThemeName . '/' . FILE_THEME))
    {
        include(DIR_THEMES . $strThemeName . '/' . FILE_THEME);
    }
    else
        spgm_Warning('unable to load ' . DIR_THEMES . $strThemeName . '/' . FILE_THEME . ': ' . WARNMSG_FILE_INSUFFICIENT_PERMISSIONS . '<br />');
}

################################################################################
# Loads textual ressources from an SPGM language file.

function spgm_LoadLanguage($strCountryCode)
{
    global $spgm_cfg;

    spgm_Trace('<p>funtion spgm_LoadLanguage</p>' . "\n" . 'country code: ' . $strCountryCode . '<br />' . "\n");

    if ($strCountryCode != '')
    {
        $filename_lang = DIR_LANG . FILE_LANG . '.' . $strCountryCode;
        if (file_exists($filename_lang))
        {
            if (spgm_CheckPerms($filename_lang))
            {
                include($filename_lang);
            }
        }
        else
            spgm_Warning('No support for lang. ' . $strCountryCode . ' &raquo; default: english<br />');
    }
}


###############################################################################
# Loads picture/thumbnail captions for a given gallery

function spgm_LoadPictureCaptions($strGalleryId)
{
    global $spgm_cfg;

    spgm_Trace('<p>funtion spgm_LoadPictureCaption</p>' . "\n" . 'strGalleryId: ' . $strGalleryId . '<br />' . "\n");


    $strCaptionsFilename = DIR_GAL . $strGalleryId . '/' . FILE_PIC_CAPTIONS;
    if (spgm_CheckPerms($strCaptionsFilename))
    {
        $arrCaptions = file($strCaptionsFilename);
        $_max        = count($arrCaptions);
        for ($i = 0; $i < $_max; $i++)
        {
            // are we on a line that should append the current caption ?
            if ($arrCaptions[$i][0] == CAPTION_KEEPER AND $strCurrentPicture != '')
            {
                $spgm_cfg['captions'][$strCurrentPicture] .= substr(trim($arrCaptions[$i]), strlen(CAPTION_KEEPER));
            }
            elseif (strpos($arrCaptions[$i], CAPTION_DELIMITER) !== false)
            {
                list($strPictureFilename, $strCaption) = explode(CAPTION_DELIMITER, $arrCaptions[$i]);
                $strCurrentPicture                        = trim($strPictureFilename);
                $spgm_cfg['captions'][$strCurrentPicture] = trim($strCaption);
            }
        }
    }
}

##################################################################
# Loads Exif Data from and returns it as an XHTML-formatted string

function spgm_LoadExif($strPictureURL)
{
    global $spgm_cfg;

    $arrExifData = exif_read_data($strPictureURL);
    $strExifData = '';

    if ($spgm_cfg['conf']['exifInfo'][0] == 'ALL')
    {
        foreach ($arrExifData as $key => $value)
        {
            if (!is_array($arrExifData[$key]))
            {
                $strExifData .= '&lt;b&gt;' . $key . '&lt;/b&gt; ' . $value . '&lt;br /&gt;';
            }
        }
        $strExifData = str_replace("\n", '', $strExifData);
    }
    else
    {
        $max = count($spgm_cfg['conf']['exifInfo']);
        for ($i = 0; $i < $max; $i++)
        {
            $key = $spgm_cfg['conf']['exifInfo'][$i];
            $strExifData .= '&lt;b&gt;' . $key . '&lt;/b&gt; ' . $arrExifData[$key] . '&lt;br /&gt;';
        }
    }

    return $strExifData;
}


################################################################################

function spgm_PostInitCheck()
{
    global $spgm_cfg;

    spgm_Trace('<p>funtion spgm_PostInitCheck</p>' . "\n");

    $_mix = $spgm_cfg['conf']['newStatusDuration'];
    if (!is_int($_mix) || ($_mix < 0))
        spgm_Error('spgm_cfg[conf][newStatusDuration]: ' . ERRMSG_INVALID_VALUE);

    $_mix = $spgm_cfg['conf']['thumbnailsPerPage'];
    if (!is_int($_mix) || ($_mix < 1))
        spgm_Error('spgm_cfg[conf][thumbnailsPerPage]: ' . ERRMSG_INVALID_VALUE);

    $_mix = $spgm_cfg['conf']['thumbnailsPerRow'];
    if (!is_int($_mix) || ($_mix < 1))
        spgm_Error('spgm_cfg[conf][thumbnailsPerRow]: ' . ERRMSG_INVALID_VALUE);

    $_mix = $spgm_cfg['conf']['galleryListingCols'];
    if (!is_int($_mix) || ($_mix < 1))
        spgm_Error('spgm_cfg[conf][galleryListingCols]: ' . ERRMSG_INVALID_VALUE);

    $_mix = $spgm_cfg['conf']['subGalleryLevel'];
    if (!is_int($_mix) || ($_mix < 0))
        spgm_Error('spgm_cfg[conf][subGalleryLevel]: ' . ERRMSG_INVALID_VALUE);

    $_mix = $spgm_cfg['conf']['galleryIconType'];
    if (!is_int($_mix) || ($_mix != GALICON_NONE && $_mix != GALICON_RANDOM))
        spgm_Error('spgm_cfg[conf][galleryIconType]: ' . ERRMSG_INVALID_VALUE);

    $_mix = $spgm_cfg['conf']['galleryIconHeight'];
    if (!is_int($_mix) || ($_mix < ORIGINAL_SIZE))
        spgm_Error('spgm_cfg[conf][galleryIconHeight]: ' . ERRMSG_INVALID_VALUE);

    $_mix = $spgm_cfg['conf']['galleryIconWidth'];
    if (!is_int($_mix) || ($_mix < ORIGINAL_SIZE))
        spgm_Error('spgm_cfg[conf][galleryIconWidth]: ' . ERRMSG_INVALID_VALUE);

    $_mix = $spgm_cfg['conf']['galleryCaptionPos'];
    if (!is_int($_mix) || ($_mix != RIGHT && $_mix != BOTTOM))
        spgm_Error('spgm_cfg[conf][galleryCaptionPos]: ' . ERRMSG_INVALID_VALUE);

    $_mix = $spgm_cfg['conf']['galleryOrientation'];
    if (!is_int($_mix) || ($_mix != ORIENTATION_TOPBOTTOM && $_mix != ORIENTATION_LEFTRIGHT))
        spgm_Error('spgm_cfg[conf][galleryOrientation]: ' . ERRMSG_INVALID_VALUE);

    $_mix = $spgm_cfg['conf']['gallerySortType'];
    if (!is_int($_mix) || ($_mix != SORTTYPE_CREATION_DATE && $_mix != SORTTYPE_NAME))
        spgm_Error('spgm_cfg[conf][gallerySortType]: ' . ERRMSG_INVALID_VALUE);

    $_mix = $spgm_cfg['conf']['gallerySortOptions'];
    if (!is_int($_mix) || ($_mix != SORT_ASCENDING && $_mix != SORT_DESCENDING))
        spgm_Error('spgm_cfg[conf][gallerySortOptions]: ' . ERRMSG_INVALID_VALUE);

    $_mix = $spgm_cfg['conf']['pictureSortType'];
    if (!is_int($_mix) || ($_mix != SORTTYPE_CREATION_DATE && $_mix != SORTTYPE_NAME))
        spgm_Error('spgm_cfg[conf][pictureSortType]: ' . ERRMSG_INVALID_VALUE);

    $_mix = $spgm_cfg['conf']['pictureSortOptions'];
    if (!is_int($_mix) || ($_mix != SORT_ASCENDING && $_mix != SORT_DESCENDING))
        spgm_Error('spgm_cfg[conf][pictureSortOptions]: ' . ERRMSG_INVALID_VALUE);

    if (!is_bool($spgm_cfg['conf']['pictureInfoedThumbnails']))
        spgm_Error('spgm_cfg[conf][pictureInfoedThumbnail]: ' . ERRMSG_INVALID_VALUE);

    if (!is_bool($spgm_cfg['conf']['captionedThumbnails']))
        spgm_Error('spgm_cfg[conf][captionedThumbnails]: ' . ERRMSG_INVALID_VALUE);

    if (!is_bool($spgm_cfg['conf']['pictureCaptionedThumbnails']))
        spgm_Error('spgm_cfg[conf][pictureCaptionedThumbnails]: ' . ERRMSG_INVALID_VALUE);

    $_mix = $spgm_cfg['conf']['fullPictureWidth'];
    if (!is_int($_mix) || ($_mix < ORIGINAL_SIZE))
        spgm_Error('spgm_cfg[conf][fullPictureWidth]: ' . ERRMSG_INVALID_VALUE);

    $_mix = $spgm_cfg['conf']['fullPictureHeight'];
    if (!is_int($_mix) || ($_mix < ORIGINAL_SIZE))
        spgm_Error('spgm_cfg[conf][fullPictureHeight]: ' . ERRMSG_INVALID_VALUE);

    if (!is_bool($spgm_cfg['conf']['popupOverFullPictures']))
        spgm_Error('spgm_cfg[conf][popupOverFullPictures]: ' . ERRMSG_INVALID_VALUE);

    if (!is_bool($spgm_cfg['conf']['popupPictures']))
        spgm_Error('spgm_cfg[conf][popupPictures]: ' . ERRMSG_INVALID_VALUE);

    $_mix = $spgm_cfg['conf']['popupWidth'];
    if (!is_int($_mix) || $_mix < 1)
        spgm_Error('spgm_cfg[conf][popupWidth]: ' . ERRMSG_INVALID_VALUE);

    $_mix = $spgm_cfg['conf']['popupHeight'];
    if (!is_int($_mix) || $_mix < 1)
        spgm_Error('spgm_cfg[conf][popupHeight]: ' . ERRMSG_INVALID_VALUE);

    if (!is_string($spgm_cfg['conf']['filters']))
        spgm_Error('spgm_cfg[conf][filters]: ' . ERRMSG_INVALID_VALUE);

    if (!is_array($spgm_cfg['conf']['zoomFactors']))
        spgm_Error('spgm_cfg[conf][zoomFactors]: ' . ERRMSG_INVALID_VALUE);

    if (!is_array($spgm_cfg['conf']['exifInfo']))
        spgm_Error('spgm_cfg[conf][exifInfo]: ' . ERRMSG_INVALID_VALUE);

    if (!is_string($spgm_cfg['conf']['theme']))
        spgm_Error('spgm_cfg[conf][theme]: ' . ERRMSG_INVALID_VALUE);

    if (!is_string($spgm_cfg['conf']['language']))
        spgm_Error('spgm_cfg[conf][language]: ' . ERRMSG_INVALID_VALUE);



    # Link labels initialization

    $arrIconInfo = array(
        // key in $spgm_cfg | ALT value | CLASS value | alternative (if resource is N/A)
        array(
            'gallerySmallIcon',
            '',
            CLASS_IMG_FOLDER,
            ''
        ),
        array(
            'galleryBigIcon',
            '',
            CLASS_IMG_FOLDER,
            '&raquo;'
        ),
        array(
            'previousPageIcon',
            'Previous thumbnail page',
            CLASS_IMG_THMBNAVI_PREV,
            '&laquo;'
        ),
        array(
            'previousPageIconNot',
            'Disabled previous thumbnail page',
            CLASS_IMG_THMBNAVI_PREV,
            '&laquo;'
        ),
        array(
            'firstPageIcon',
            'First thumbnail page',
            CLASS_IMG_THMBNAVI_PREV,
            '&laquo;&laquo;'
        ),
        array(
            'firstPageIconNot',
            'Disabled first thumbnail page',
            CLASS_IMG_THMBNAVI_PREV,
            '&laquo;&laquo;'
        ),
        array(
            'nextPageIcon',
            'Next thumbnail page',
            CLASS_IMG_THMBNAVI_NEXT,
            '&raquo;'
        ),
        array(
            'nextPageIconNot',
            'Disabled next thumbnail page',
            CLASS_IMG_THMBNAVI_NEXT,
            '&raquo;'
        ),
        array(
            'lastPageIcon',
            'Last thumbnail page',
            CLASS_IMG_THMBNAVI_NEXT,
            '&raquo;&raquo;'
        ),
        array(
            'lastPageIconNot',
            'Disabled last thumbnail page',
            CLASS_IMG_THMBNAVI_NEXT,
            '&raquo;&raquo;'
        ),
        array(
            'previousPictureIcon',
            'Previous picture',
            CLASS_IMG_PICTURE_PREV,
            '&laquo;'
        ),
        array(
            'nextPictureIcon',
            'Next picture',
            CLASS_IMG_PICTURE_NEXT,
            '&raquo;'
        ),
        array(
            'newItemIcon',
            '',
            CLASS_IMG_NEW,
            ''
        )
    );

    $dim             = array();
    $iIconNumber     = count($arrIconInfo);
    $strIconFileName = '';
    $_key            = '';
    $_lblAlt         = '';
    $_lblClass       = '';
    $_lblNa          = '';

    for ($i = 0; $i < $iIconNumber; $i++)
    {

        $_key            = $arrIconInfo[$i][0];
        $_lblAlt         = $arrIconInfo[$i][1];
        $_lblClass       = $arrIconInfo[$i][2];
        $_lblNa          = $arrIconInfo[$i][3];
        $strIconFileName = DIR_THEMES . $spgm_cfg['conf']['theme'] . '/' . $spgm_cfg['theme'][$_key];

        if ($spgm_cfg['theme'][$_key] != '' && spgm_CheckPerms($strIconFileName))
        {
            $dim                      = getimagesize($strIconFileName);
            $spgm_cfg['theme'][$_key] = '<img src="' . $strIconFileName . '"';
            $spgm_cfg['theme'][$_key] .= ' alt="' . $_lblAlt . '"';
            $spgm_cfg['theme'][$_key] .= ' class="' . $_lblClass . '"';
            $spgm_cfg['theme'][$_key] .= ' width="' . $dim[0] . '"';
            $spgm_cfg['theme'][$_key] .= ' height="' . $dim[1] . '" />';
        }
        else
        {
            if ($_lblNa != '')
                $spgm_cfg['theme'][$_key] = $_lblNa;
        }
    }
}


################################################################################
# Loads config files from the possible different locations.
# To allow properties inheritance, it includes all the config file from the
# top level gallery to the gallery itself.
# TODO: support for INI files (PHP4) ?

function spgm_LoadConfig($strGalleryId)
{
    global $spgm_cfg;

    spgm_Trace('<p>funtion spgm_LoadConfig</p>' . "\n" . 'strGalleryId: ' . $strGalleryId . '<br />' . "\n");


    if (spgm_IsGallery($strGalleryId))
    {

        // always load the default config file
        $strConfigurationFileName = DIR_GAL . FILE_CONF;

        if (spgm_CheckPerms($strConfigurationFileName))
        {
            include($strConfigurationFileName);
        }

        // now, include all the possible config files
        if ($strGalleryId != '')
        {
            $strConfigurationPathElements = explode('/', $strGalleryId);
            $iPathDepth                   = count($strConfigurationPathElements);
            $_strConfigurationPath        = ''; // grows inside the follwing loop ("gal1" -> "gal1/gal2"...)
            for ($i = 0; $i < $iPathDepth; $i++) // use "foreach ($strConfigurationPathElements as $dir_name) {" in PHP4
            {
                $_strConfigurationPath .= $strConfigurationPathElements[$i] . '/';
                $strConfigurationFileName = DIR_GAL . $_strConfigurationPath . FILE_CONF;
                if (spgm_CheckPerms($strConfigurationFileName))
                {
                    include($strConfigurationFileName);
                }
            }
        }
    }

    spgm_LoadLanguage($spgm_cfg['conf']['language']);
    spgm_LoadFlavor($spgm_cfg['conf']['theme']);
    spgm_PostInitCheck();

}



################################################################################
# returns an array containing various information for a given gallery and its
# provided pictures.
# returned array:
# $array[0] = total number of pictures
# $array[1] = number of new pictures
# $array[2] = the thumbnail's filename to use for the gallery icon

function spgm_GetGalleryInfo($strGalleryId, $arrPictureFilenames)
{
    global $spgm_cfg;

    $iPictureNumber     = 0;
    $iNewPictureNumber  = 0;
    $strPathToGalleries = DIR_GAL . $strGalleryId;
    $iPictureNumber     = count($arrPictureFilenames);
    $iNewPictureNumber  = 0;
    for ($i = 0; $i < $iPictureNumber; $i++)
        if (spgm_IsNew($strPathToGalleries . '/' . $arrPictureFilenames[$i]))
            $iNewPictureNumber++;

    spgm_Trace('<p>function spgm_GetGalleryInfo</p>' . "\n" . 'strGalleryId: ' . $strGalleryId . '<br />' . "\n" . 'iPictureNumber: ' . $iPictureNumber . '<br />' . "\n" . 'strPathToGalleries: ' . $strPathToGalleries . '<br />' . "\n");

    $arrGalleryInfo[0] = $iPictureNumber;
    $arrGalleryInfo[1] = $iNewPictureNumber;
    if ($spgm_cfg['conf']['galleryIconType'] == GALICON_RANDOM && $iPictureNumber > 0)
        @$arrGalleryInfo[2] = $arrPictureFilenames[rand(0, $iPictureNumber - 1)];
    else
        $arrGalleryInfo[2] = '';
    return $arrGalleryInfo;
}


###############################################################################
# Callback function used to sort galleries/pictures against modification date
# The two parameters are automatically passed by the usort() function

function spgm_CallbackCompareMTime($strFilePath1, $strFilePath2)
{
    global $spgm_cfg;

    if (!strcmp($strFilePath1, $strFilePath2))
        return 0;

    return (filemtime($spgm_cfg['global']['tmpPathToPics'] . $strFilePath1) > filemtime($spgm_cfg['global']['tmpPathToPics'] . $strFilePath2)) ? 1 : -1;
}



################################################################################
# Creates a sorted array containing first level sub-galleries of a given gallery
# $galid - the gallery ID to introspect
# $display - boolean indicating that galleries will be rendered and that sort
#			 options consequently have to be turned on
# returns: a sorted array containing the sub-gallery filenames for the given
#			 gallery

function spgm_CreateGalleryArray($strGalleryId, $bToBeDisplayed)
{
    global $spgm_cfg;

    $strPathToGallery = DIR_GAL . $strGalleryId;

    spgm_Trace('<p>function spgm_CreateGalleryArray</p>' . "\n" . 'strGalleryId: ' . $strGalleryId . '<br />' . "\n" . 'strPathToGallery: ' . $strPathToGallery . '<br />' . "\n" . 'bToBeDisplayed: ' . $bToBeDisplayed . '<br />' . "\n");

    if (spgm_IsGallery($strGalleryId))
        $_hDir = @opendir($strPathToGallery);
    else
        spgm_Error($strGalleryId . ': ' . ERRMSG_UNKNOWN_GALLERY);
    if ($strGalleryId != '')
        $strGalleryId .= '/'; // little hack

    if ($strPathToGallery == DIR_GAL)
        $strSortFilePath = $strPathToGallery . FILE_GAL_SORT;
    else
        $strSortFilePath = $strPathToGallery . '/' . FILE_GAL_SORT;

    $arrSubGalleries = array();
    if (spgm_CheckPerms($strSortFilePath))
    {
        $strGalleryNames = file($strSortFilePath);
        $iGalleryNumber  = count($strGalleryNames);
        for ($i = 0; $i < $iGalleryNumber; $i++)
        {
            $strGalleryName = trim($strGalleryNames[$i]);
            if (spgm_IsGallery($strGalleryId . $strGalleryName))
                $arrSubGalleries[] = $strGalleryName;
        }
    }
    else
    {
        while (false !== ($_strFilename = readdir($_hDir)))
        {
            if ($_strFilename != '.' && $_strFilename != '..' && spgm_IsGallery($strGalleryId . $_strFilename))
            {
                // add the gallery to the list if not hidden
                if (!file_exists($strPathToGallery . '/' . $_strFilename . '/' . FILE_GAL_HIDE))
                {
                    $arrSubGalleries[] = $_strFilename;
                }
            }
        }
        closedir($_hDir);

        // Apply sort options if needed
        if ($bToBeDisplayed)
        {
            if (count($arrSubGalleries) > 0)
            {
                if ($spgm_cfg['conf']['gallerySortType'] == SORTTYPE_NAME)
                {
                    if ($spgm_cfg['conf']['gallerySortOptions'] == SORT_DESCENDING)
                        rsort($arrSubGalleries);
                    else
                        sort($arrSubGalleries);
                }
                elseif ($spgm_cfg['conf']['gallerySortType'] == SORTTYPE_CREATION_DATE)
                {
                    $spgm_cfg['global']['tmpPathToPics'] = DIR_GAL . $strGalleryId;
                    usort($arrSubGalleries, 'spgm_CallbackCompareMTime'); // TODO: omit it ?
                    if ($spgm_cfg['conf']['gallerySortOptions'] == SORT_DESCENDING)
                        $arrSubGalleries = array_reverse($arrSubGalleries);
                }
            }
        }
    }
    return $arrSubGalleries;
}


################################################################################
# Creates a sorted array of the pictures to diplay for a given gallery
# $galid - the gallery ID (must be always valid)
# $filter - the filter that defines the pictures to include in the list
# $display - boolean indicating that thumbnails will be rendered and that sort
#			 options consequently have to be turned on
# returns: a sorted array containing the thumbnails' basenames of the gallery

function spgm_CreatePictureArray($strGalleryId, $strFilterFlags, $bForDisplayPurpose)
{
    global $spgm_cfg;

    $strPathToGallery = DIR_GAL . $strGalleryId . '/';
    $hDir             = opendir($strPathToGallery);

    spgm_Trace('<p>function spgm_CreatePictureArray</p>' . "\n" . 'strGalleryId: ' . $strGalleryId . '<br />' . "\n" . 'strFilterFlags: ' . $strFilterFlags . '<br />' . "\n" . 'strPathToGallery: ' . $strPathToGallery . '<br />' . "\n" . 'bForDisplayPurpose: ' . $bForDisplayPurpose . '<br />' . "\n");

    $arrPictureFilenames = array();
    $strPathToSortFile   = $strPathToGallery . FILE_PIC_SORT;
    if (spgm_CheckPerms($strPathToSortFile))
    {
        $arrSortedPictureFilenames = file($strPathToSortFile);
        $_max                      = count($arrSortedPictureFilenames);
        for ($i = 0; $i < $_max; $i++)
        {
            $strPictureName = trim($arrSortedPictureFilenames[$i]);
            if (spgm_IsPicture($strPictureName, $strGalleryId))
            {
                if (strstr($strFilterFlags, PARAM_VALUE_FILTER_NEW))
                {
                    if (spgm_IsNew($strPathToGallery . $strPictureName))
                        $arrPictureFilenames[] = $strPictureName;
                }
                else
                    $arrPictureFilenames[] = $strPictureName;
            }
        }
    }
    else
    {
        while (false !== ($strFileName = readdir($hDir)))
        {
            if (spgm_IsPicture($strFileName, $strGalleryId))
            {
                if (strstr($strFilterFlags, PARAM_VALUE_FILTER_NEW))
                {
                    if (spgm_IsNew($strPathToGallery . $strFileName))
                        $arrPictureFilenames[] = $strFileName;
                }
                else
                    $arrPictureFilenames[] = $strFileName;
            }
        }
        closedir($hDir);

        // Apply sort optionsif needed
        if ($bForDisplayPurpose)
        {
            if (count($arrPictureFilenames) > 0)
            {
                if ($spgm_cfg['conf']['pictureSortType'] == SORTTYPE_NAME)
                {
                    if ($spgm_cfg['conf']['pictureSortOptions'] == SORT_DESCENDING)
                        rsort($arrPictureFilenames);
                    else
                        sort($arrPictureFilenames);
                }
                elseif ($spgm_cfg['conf']['pictureSortType'] == SORTTYPE_CREATION_DATE)
                {
                    $spgm_cfg['global']['tmpPathToPics'] = $strPathToGallery;
                    usort($arrPictureFilenames, 'spgm_CallbackCompareMTime'); // TODO: omit it ?
                    if ($spgm_cfg['conf']['pictureSortOptions'] == SORT_DESCENDING)
                        $arrPictureFilenames = array_reverse($arrPictureFilenames);
                }
            }
        }
    }

    return $arrPictureFilenames;
}


################################################################################

function spgm_DisplayThumbnailNavibar($iCurrentPageIndex, $iPageNumber, $strGalleryId, $strFilterFlags)
{
    global $spgm_cfg;

    spgm_Trace('<p>function spgm_DisplayThumbnailNavibar</p>' . "\n" . 'iCurrentPageIndex: ' . $iCurrentPageIndex . '<br />' . "\n" . 'iPageNumber: ' . $iPageNumber . '<br />' . "\n" . 'strGalleryId: ' . $strGalleryId . '<br />' . "\n");

    // display left arrows
    if ($iCurrentPageIndex > 1)
    {
        $iPreviousPageIndex = $iCurrentPageIndex - 1;
        print spgm_BuildLink($spgm_cfg['theme']['firstPageIcon'], '', '', $strGalleryId, 1, -1, str_replace(PARAM_VALUE_FILTER_SLIDESHOW, '', $strFilterFlags));
        print '&nbsp; ';
        print spgm_BuildLink($spgm_cfg['theme']['previousPageIcon'], '', '', $strGalleryId, $iPreviousPageIndex, -1, str_replace(PARAM_VALUE_FILTER_SLIDESHOW, '', $strFilterFlags));
    }
    else
    {
        print ' ' . $spgm_cfg['theme']['firstPageIconNot'];
        print ' &nbsp; ' . $spgm_cfg['theme']['previousPageIconNot'];
    }
    print ' &nbsp; ';

    // display the page numbers
    for ($i = 1; $i <= $iPageNumber; $i++)
    {
        if ($i != $iCurrentPageIndex)
            print spgm_BuildLink($i, 'navi', '', $strGalleryId, $i, -1, str_replace(PARAM_VALUE_FILTER_SLIDESHOW, '', $strFilterFlags));
        else
            print $i; // don't make it an anchor if this is the current page
        if ($i < $iPageNumber)
            print ' &nbsp; ';
    }

    // display right arrows
    print ' &nbsp;';
    if ($iCurrentPageIndex < $iPageNumber)
    {
        $iNextPageIndex = $iCurrentPageIndex + 1;
        print spgm_BuildLink($spgm_cfg['theme']['nextPageIcon'], '', '', $strGalleryId, $iNextPageIndex, -1, str_replace(PARAM_VALUE_FILTER_SLIDESHOW, '', $strFilterFlags));
        print '&nbsp; ';
        print spgm_BuildLink($spgm_cfg['theme']['lastPageIcon'], '', '', $strGalleryId, $iPageNumber, -1, str_replace(PARAM_VALUE_FILTER_SLIDESHOW, '', $strFilterFlags));
    }
    else
    {
        print ' ' . $spgm_cfg['theme']['nextPageIconNot'];
        print '	 ' . $spgm_cfg['theme']['lastPageIconNot'];
    }
}

################################################################################

function spgm_DisplayFilterToggles($strGalleryId, $strFilterFlags, $arrGalleryInfo)
{
    global $spgm_cfg;

    spgm_Trace('<p>function spgm_DisplayFilterToggles</p>' . "\n" . 'strGalleryId: ' . $strGalleryId . '<br />' . "\n" . 'strFilterFlags: ' . $strFilterFlags . '<br />' . "\n");

    $strHtmlToggles = '';
    $bFilterNewOn   = strstr($strFilterFlags, PARAM_VALUE_FILTER_NEW);
    if (($arrGalleryInfo[1] > 0 && $arrGalleryInfo[0] != $arrGalleryInfo[1]) || $bFilterNewOn)
    {
        if ($bFilterNewOn)
        {
            $strHtmlToggles .= spgm_BuildLink($spgm_cfg['locale']['filterAll'], '', '', $strGalleryId, -1, -1, str_replace(PARAM_VALUE_FILTER_NEW, '', $strFilterFlags));
        }
        else
        {
            $strHtmlToggles .= spgm_BuildLink($spgm_cfg['locale']['filterNew'], '', '', $strGalleryId, -1, -1, str_replace(PARAM_VALUE_FILTER_SLIDESHOW, '', $strFilterFlags) . PARAM_VALUE_FILTER_NEW);
        }

        print ' &nbsp;&nbsp;<span class="' . CLASS_SPAN_FILTERS . '">[' . $spgm_cfg['locale']['filter'] . ' &raquo; ' . $strHtmlToggles . ']</span>' . "\n";
    }
}


################################################################################
# Prerequisite: spgm_IsGallery($galid) == true

function spgm_DisplayGalleryNavibar($strGalleryId, $strFilterFlags, $mixPictureId = '', $arrPictureFilenames)
{
    global $spgm_cfg;

    spgm_Trace('<p>function spgm_DisplayGalleryNavibar</p>' . "\n" . 'strGalleryId: ' . $strGalleryId . '<br />' . "\n" . 'strFilterFlags: ' . $strFilterFlags . '<br />' . "\n" . 'mixPictureId: ' . $mixPictureId . '<br />' . "\n");

    $arrExplodedPathToGallery = explode('/', $strGalleryId);

    print '	 <div class="' . CLASS_DIV_GALHEADER . '">' . "\n";

    // display main gallery link
    $filters = '';
    if ($spgm_cfg['global']['propagateFilters'])
    {
        $filters = str_replace(PARAM_VALUE_FILTER_SLIDESHOW, '', $strFilterFlags);
    }
    if ($spgm_cfg['theme']['gallerySmallIcon'] != '')
    {
        print spgm_BuildLink($spgm_cfg['theme']['gallerySmallIcon'], CLASS_TD_GALITEM_TITLE, '', '', -1, -1, $filters);
    }
    else
    {
        print spgm_BuildLink($spgm_cfg['locale']['rootGallery'], CLASS_TD_GALITEM_TITLE, '', '', -1, -1, $filters);
    }

    // display each gallery of the hierarchy
    $strHtmlGalleryLink = $arrExplodedPathToGallery[0]; // to avoid the first '/'
    $_max               = count($arrExplodedPathToGallery);
    $_strGalleryId      = '';

    for ($i = 0; $i < $_max; $i++)
    {
        $_strGalleryId .= $arrExplodedPathToGallery[$i] . '/';
        $_strPathToGallery      = DIR_GAL . $_strGalleryId;
        $_strPathToGalleryTitle = $_strPathToGallery . FILE_GAL_TITLE;
        $strHtmlGalleryName     = '';
        if (spgm_CheckPerms($_strPathToGalleryTitle))
        {
            $arrTitle           = file($_strPathToGalleryTitle);
            $strHtmlGalleryName = $arrTitle[0];
        }
        else
        {
            $strHtmlGalleryName = str_replace('_', ' ', $arrExplodedPathToGallery[$i]);
        }

        print ' &raquo; ';

		// check if system is logged
		$loglink = is_logged($arrExplodedPathToGallery[$i]) ? '<a href="log.php?system=' . urlencode($arrExplodedPathToGallery[$i]) . '" style="color:inherit" title="System has log entries"><img src="/style/img/log.png" style="margin-left:5px" /></a>' : "";

		// show link if system exists
		$sysinfo_link = system_exists($arrExplodedPathToGallery[$i]) ? '<a href="system.php?system_name=' . urlencode($arrExplodedPathToGallery[$i]) . '" style="color:inherit" title="System info"><img src="/style/img/info.png" style="margin-left:5px" /></a>' : "";

        if ($i < ($_max - 1))
        {
            print spgm_BuildLink($strHtmlGalleryName, CLASS_DIV_GALHEADER, '', $strHtmlGalleryLink, -1, -1, $filters);
            $strHtmlGalleryLink .= '/' . $arrExplodedPathToGallery[$i + 1];
        }
        else
        {
            // Final gallery display
            $iCurrentPageIndex = 1;

            if (strstr($strFilterFlags, PARAM_VALUE_FILTER_NOTHUMBS) || strstr($strFilterFlags, PARAM_VALUE_FILTER_SLIDESHOW))
            {
                if ($mixPictureId == '')
                {
                    print $strHtmlGalleryName;
                }
                else
                {
                    $iCurrentPageIndex = ((int) ($mixPictureId / $spgm_cfg['conf']['thumbnailsPerPage'])) + 1;
                    print spgm_BuildLink($strHtmlGalleryName, CLASS_DIV_GALHEADER, '', $strHtmlGalleryLink, $iCurrentPageIndex, -1, str_replace(PARAM_VALUE_FILTER_SLIDESHOW, '', $strFilterFlags));
                }
            }
            else
            {
                print $strHtmlGalleryName;
            }
        }
		print $sysinfo_link.$loglink;
    }

    // Notify if we are in "new picture mode"
    if (strstr($strFilterFlags, PARAM_VALUE_FILTER_NEW))
        print ' (' . $spgm_cfg['locale']['newPictures'] . ')';

    // Link to slideshow mode
    if ($spgm_cfg['conf']['enableSlideshow'] == true)
    {
        if (!strstr($strFilterFlags, PARAM_VALUE_FILTER_SLIDESHOW) && count($arrPictureFilenames) > 0)
        {
            print ' [';
            print spgm_BuildLink($spgm_cfg['locale']['filterSlideshow'], CLASS_DIV_GALHEADER, '', $strHtmlGalleryLink, $iCurrentPageIndex, 0, str_replace(PARAM_VALUE_FILTER_SLIDESHOW, '', $strFilterFlags) . PARAM_VALUE_FILTER_SLIDESHOW);
            print ']';
        }
    }


    print "\n" . '		</div>' . "\n";

}


################################################################################
# Recursive function to display all galleries as a hierarchy

function spgm_DisplayGalleryHierarchy($strGalleryId, $iGalleryDepth, $strFilterFlags)
{
    global $spgm_cfg;

    $strPathToGallery = DIR_GAL . $strGalleryId;

    spgm_Trace('<p>function spgm_DisplayGalleryHierarchy</p>' . "\n" . 'strGalleryId: ' . $strGalleryId . '<br />' . "\n" . 'iGalleryDepth: ' . $iGalleryDepth . '<br />' . "\n" . 'strFilterFlags: ' . $strFilterFlags . '<br />' . "\n" . 'strPathToGallery: ' . $strPathToGallery . '<br />' . "\n");

    $strHtmlOffset = '';

    // check for super gallery.
    if ($strGalleryId == '')
    {
        $strPathToSuperGallery = '';
    }
    else
    {
        $strPathToSuperGallery = $strGalleryId . '/';
        for ($i = 0; $i < $iGalleryDepth; $i++)
            $strHtmlOffset .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    }

    # 'new' label tuning according to the actual new item
    if ($spgm_cfg['theme']['newItemIcon'] != '')
    {
        $strHtmlNewGallery  = $spgm_cfg['theme']['newItemIcon'];
        $strHtmlNewPictures = $spgm_cfg['theme']['newItemIcon'];
        $strNewPicture      = $spgm_cfg['theme']['newItemIcon'];
    }
    else
    {
        $strHtmlSpanNewItem = '<span style="color: #ffd600">';
        $strHtmlNewGallery  = $strHtmlSpanNewItem . $spgm_cfg['locale']['newGallery'] . '</span>';
        $strHtmlNewPictures = $strHtmlSpanNewItem . $spgm_cfg['locale']['newPictures'] . '</span>';
        $strNewPicture      = $strHtmlSpanNewItem . $spgm_cfg['locale']['newPicture'] . '</span>';
    }

    $arrSubGalleryFilenames = spgm_CreateGalleryArray($strGalleryId, true);
    $_max                   = count($arrSubGalleryFilenames);

    if ($iGalleryDepth == 1 && $_max > 0)
    {
        print '<table class="' . CLASS_TABLE_GALLISTING_GRID . '">' . "\n";
        print '<tr>' . "\n";
    }

    for ($i = 0; $i < $_max; $i++)
    {
        $strGalleryName              = $arrSubGalleryFilenames[$i]; //*
        $strPathToSubGallery         = $strPathToSuperGallery . $strGalleryName; //*
        $strPathToGalleryTitle       = $strPathToGallery . '/' . $strGalleryName . '/' . FILE_GAL_TITLE;
        $strGalleryThumbnailBasename = DIR_GAL . $strPathToSuperGallery . PREF_THUMB . $strGalleryName;
        $strHtmlGalleryName          = '';
        if (spgm_CheckPerms($strPathToGalleryTitle))
        {
            $arrTitle           = file($strPathToGalleryTitle);
            $strHtmlGalleryName = $arrTitle[0];
        }
        else
        {
            $strHtmlGalleryName = str_replace('_', ' ', $strGalleryName);
        }
        $arrPictureFilenames      = spgm_CreatePictureArray($strPathToSubGallery, '', false); // no filter is provided to get all the pictures
        $arrGalleryInfo           = spgm_GetGalleryInfo($strPathToSubGallery, $arrPictureFilenames);
        $iPictureNumber           = $arrGalleryInfo[0];
        $iNewPictureNumber        = $arrGalleryInfo[1];
        $strRandomPictureFilename = $arrGalleryInfo[2];

        // should never happen
        if ($iPictureNumber < 0 || $iNewPictureNumber < 0)
            spgm_Error('Error while generating gallery ' + ERRMSG_INVALID_NUMBER_OF_PICTURES);

        else
        {
            if ($spgm_cfg['conf']['thumbnailsPerPage'] > 0)
                $strUrlParamPage = '&amp;' . PARAM_NAME_PAGE . '=1';
            if ($iPictureNumber == 0)
                $strHtmlPictureNumber = '';
            else
            {
                if ($iPictureNumber > 1)
                    $strHtmlPictureNumber = '&nbsp;&nbsp;[' . $iPictureNumber . ' ' . $spgm_cfg['locale']['pictures'];
                else
                    $strHtmlPictureNumber = '&nbsp;&nbsp;[' . $iPictureNumber . ' ' . $spgm_cfg['locale']['picture'];
                $bAllPicturesNew = ($iPictureNumber == $iNewPictureNumber);
                if ($bAllPicturesNew)
                    $strHtmlPictureNumber = $strHtmlNewGallery . ' ' . $strHtmlPictureNumber;
                if ($iNewPictureNumber > 0 && !$bAllPicturesNew)
                {
                    $strHtmlPictureNumber .= ' - ' . $iNewPictureNumber . ' ';
                    $filters = '';
                    if ($spgm_cfg['global']['propagateFilters'])
                    {
                        $filters = str_replace(PARAM_VALUE_FILTER_SLIDESHOW, '', $strFilterFlags);
                    }
                    if (!strstr($strFilterFlags, PARAM_VALUE_FILTER_NEW))
                    {
                        $filters .= PARAM_VALUE_FILTER_NEW;
                    }
                    if ($iNewPictureNumber == 1)
                    {
                        $strHtmlPictureNumber .= spgm_BuildLink($strNewPicture, '', '', $strPathToSubGallery, -1, -1, $filters);
                    }
                    else
                    {
                        $strHtmlPictureNumber .= spgm_BuildLink($strHtmlNewPictures, '', '', $strPathToSubGallery, -1, -1, $filters);
                    }
                }
                $strHtmlPictureNumber .= ']';
            }

            if ($iGalleryDepth <= 1)
            {
                if (($i % $spgm_cfg['conf']['galleryListingCols'] == 0) && ($i != 0))
                    print '		 </tr>' . "\n" . '		<tr>' . "\n";
                print '	 <td class="' . CLASS_TD_GALLISTING_CELL . '">' . "\n";
            }

            print '	 <table class="' . CLASS_TABLE_GALITEM . '">' . "\n";
            print '		 <tr>' . "\n";

            // display the gallery icon
            $iRowSpan = ($spgm_cfg['conf']['galleryCaptionPos'] == BOTTOM) ? 1 : 2;
            print '		 <td rowspan="' . $iRowSpan . '" style="vertical-align:top" class="' . CLASS_TD_GALITEM_ICON . '">' . "\n";
            if ($strHtmlOffset != '')
                print '		 ' . $strHtmlOffset . "\n";

            // look for the icon...
            $strHtmlIcon               = '';
            $bNeedDropShadows          = true; // only default icons don't need them
            // find out if there is a fixed thumbnail
            $bGalleryThumbnailFound    = false;
            $iSupportedExtensionNumber = count($spgm_cfg['global']['supportedExtensions']);
            for ($j = 0; $j < $iSupportedExtensionNumber; $j++)
            {
                $strGalleryThumbnailFilename = $strGalleryThumbnailBasename . $spgm_cfg['global']['supportedExtensions'][$j];
                if (spgm_CheckPerms($strGalleryThumbnailFilename))
                {
                    $arrPictureSize = getimagesize($strGalleryThumbnailFilename);
                    $strHtmlIcon    = '<img src="' . $strGalleryThumbnailFilename . '" width="';
                    $strHtmlIcon .= $arrPictureSize[0] . '" height="' . $arrPictureSize[1];
                    $strHtmlIcon .= '" alt="" class="' . CLASS_IMG_GALICON . '" />';
                    $bGalleryThumbnailFound = true;
                    break;
                }
            }
            if (!$bGalleryThumbnailFound)
            {
                // random thumbnails are used
                if ($strRandomPictureFilename != '')
                {
                    if (defined('DIR_THUMBS'))
                    {
                        $strGalleryThumbnailFilename = DIR_GAL . $strPathToSubGallery . '/' . DIR_THUMBS;
                        $strGalleryThumbnailFilename .= PREF_THUMB . $strRandomPictureFilename;
                    }
                    else
                    {
                        $strGalleryThumbnailFilename = DIR_GAL . $strPathToSubGallery . '/';
                        $strGalleryThumbnailFilename .= PREF_THUMB . $strRandomPictureFilename;
                    }
                    $arrPictureSize = getimagesize($strGalleryThumbnailFilename);
                    if ($spgm_cfg['conf']['galleryIconHeight'] != ORIGINAL_SIZE)
                        $strHtmlHeight = 'height="' . $spgm_cfg['conf']['galleryIconHeight'] . '"';
                    else
                    {
                        if ($spgm_cfg['conf']['galleryIconWidth'] != ORIGINAL_SIZE)
                        {
                            $iHeight       = (int) $arrPictureSize[1] * ($spgm_cfg['conf']['galleryIconWidth'] / $arrPictureSize[0]);
                            $strHtmlHeight = 'height="' . $iHeight . '"';
                        }
                        else
                            $strHtmlHeight = 'height="' . $arrPictureSize[1] . '"';
                    }

                    if ($spgm_cfg['conf']['galleryIconWidth'] != ORIGINAL_SIZE)
                        $strHtmlWidth = 'width="' . $spgm_cfg['conf']['galleryIconWidth'] . '"';
                    else
                    {
                        if ($spgm_cfg['conf']['galleryIconHeight'] != ORIGINAL_SIZE)
                        {
                            $iWidth       = (int) $arrPictureSize[0] * ($spgm_cfg['conf']['galleryIconHeight'] / $arrPictureSize[1]);
                            $strHtmlWidth = 'width="' . $iWidth . '"';
                        }
                        else
                            $strHtmlWidth = 'width="' . $arrPictureSize[0] . '"';
                    }

                    $strHtmlIcon = '<img src="' . $strGalleryThumbnailFilename . '" ';
                    $strHtmlIcon .= $strHtmlHeight . ' ' . $strHtmlWidth . ' alt="" class="';
                    $strHtmlIcon .= CLASS_IMG_GALICON . '" />';
                }
                // nor fixed and random thumbnails => default icons
                else
                {
                    $bNeedDropShadows = false;
                    if ($spgm_cfg['conf']['galleryIconType'] == GALICON_NONE)
                    {
                        $fnameGalleryIcon = $spgm_cfg['theme']['gallerySmallIcon'];
                    }
                    else
                    {
                        $fnameGalleryIcon = $spgm_cfg['theme']['galleryBigIcon'];
                    }
                    $strHtmlIcon = ($fnameGalleryIcon != '') ? $fnameGalleryIcon : '&raquo;';
                }
            }

            // display the link
            if ($bNeedDropShadows == true)
            {
                spgm_DropShadowsBeginWrap();
            }
            $filters = '';
            if ($spgm_cfg['global']['propagateFilters'])
            {
                $filters = str_replace(PARAM_VALUE_FILTER_SLIDESHOW, '', str_replace(PARAM_VALUE_FILTER_NEW, '', $strFilterFlags));
            }
            print '			 ' . spgm_BuildLink($strHtmlIcon, CLASS_TD_GALITEM_TITLE, '', $strPathToSubGallery, -1, -1, $filters) . "\n";

            if ($bNeedDropShadows == true)
            {
                spgm_DropShadowsEndWrap();
            }

            print '		 </td>' . "\n";

            if ($spgm_cfg['conf']['galleryCaptionPos'] == BOTTOM)
                print '		 </tr>' . "\n" . '		<tr>' . "\n";

			// check if system is logged
			$loglink = is_logged($strHtmlGalleryName) ? '<a href="log.php?system=' . urlencode($strHtmlGalleryName) . '" style="color:inherit" title="System has log entries"><img src="/style/img/log.png" style="margin-left:5px" /></a>' : "";

            // display the gallery title
            print '		 <td class="' . CLASS_TD_GALITEM_TITLE . '">' . "\n";
            print '			 ' . spgm_BuildLink($strHtmlGalleryName, CLASS_TD_GALITEM_TITLE, '', $strPathToSubGallery, -1, -1, $filters);
            print ' ' . $loglink.$strHtmlPictureNumber . ' ' . "\n";
            print '		 </td>' . "\n";
            print '		 </tr>' . "\n";

            // display the gallery caption
            print '		 <tr>' . "\n";
            print '		 <td class="' . CLASS_TD_GALITEM_CAPTION . '">' . "\n";
            $strPathToGalleryCaption = $strPathToGallery . '/' . $strGalleryName . '/' . FILE_GAL_CAPTION;
            if (spgm_CheckPerms($strPathToGalleryCaption)) // check perms
            {
                print '			 ';
                include($strPathToGalleryCaption);
            }
            print '		 </td>' . "\n";
            print '		 </tr>' . "\n";
            print '	 </table>' . "\n";
        }

        // TODO check this: one test ?
        if ($spgm_cfg['conf']['subGalleryLevel'] == 0)
        {
            spgm_DisplayGalleryHierarchy($strPathToSubGallery, $iGalleryDepth + 1, $strFilterFlags);
        }
        elseif ($iGalleryDepth < $spgm_cfg['conf']['subGalleryLevel'] - 1)
        {
            spgm_DisplayGalleryHierarchy($strPathToSubGallery, $iGalleryDepth + 1, $strFilterFlags);
        }

        if ($iGalleryDepth <= 1)
            print '	 </td>' . "\n";

    } // endfor

    if ($iGalleryDepth == 1 && $_max > 0)
    {
        print ' </tr>' . "\n";
        print '</table>' . "\n";
    }
}

################################################################################

function spgm_DisplayPicture($strGalleryId, $iPictureId, $strFilterFlags)
{
    global $spgm_cfg;

    $arrPictureFilenames = spgm_CreatePictureArray($strGalleryId, $strFilterFlags, true);
    $iPictureNumber      = count($arrPictureFilenames);
    $strPathToPictures   = DIR_GAL . $strGalleryId . '/';
    $strPictureFilename  = $arrPictureFilenames[$iPictureId];
    $_strFileExtension   = strrchr($strPictureFilename, '.');
    $strPictureBasename  = substr($strPictureFilename, 0, -strlen($_strFileExtension));
    $strPictureURL       = $strPathToPictures . $strPictureFilename;
    $strCaptionURL       = $strPictureURL . EXT_PIC_CAPTION; // DEPRECATED
    $strGalleryName      = str_replace('_', ' ', $strGalleryId);
    $strGalleryName      = str_replace('/', ' &raquo; ', $strGalleryName);
    $bSlideshowMode      = strstr($strFilterFlags, PARAM_VALUE_FILTER_SLIDESHOW) != false;

    if ($spgm_cfg['conf']['thumbnailsPerPage'] != 0)
    {
        $iPageNumber = $iPictureNumber / $spgm_cfg['conf']['thumbnailsPerPage'];
        if ($iPageNumber > (int) ($iPictureNumber / $spgm_cfg['conf']['thumbnailsPerPage']))
            $iPageNumber = (int) ++$iPageNumber;
    }

    spgm_Trace('<p>function spgm_DisplayPicture</p>' . "\n" . 'strGalleryId: ' . $strGalleryId . '<br />' . "\n" . 'strPictureFilename: ' . $strPictureFilename . '<br />' . "\n" . 'strPathToPictures: ' . $strPathToPictures . '<br />' . "\n" . 'strPictureURL: ' . $strPictureURL . '<br />' . "\n");


    if (($iPictureId < 0) || ($iPictureId > $iPictureNumber - 1) || $iPictureId == '')
        spgm_Error(ERRMSG_UNKNOWN_PICTURE);

    if (!spgm_IsGallery($strGalleryId))
        spgm_Error(ERRMSG_UNKNOWN_GALLERY);


    if (spgm_IsPicture($strPictureFilename, $strGalleryId))
    {
        $arrPictureDim      = getimagesize($strPictureURL);
        $iPreviousPictureId = $iPictureId - 1;
        $iNextPictureId     = $iPictureId + 1;

        // always display the gallery header
        spgm_DisplayGalleryNavibar($strGalleryId, $strFilterFlags, $iPictureId, $arrPictureFilenames);

        // thumbnails are only displayed if wanted
        if (!strstr($strFilterFlags, PARAM_VALUE_FILTER_NOTHUMBS) && !$bSlideshowMode)
        {
            spgm_DisplayThumbnails($strGalleryId, $arrPictureFilenames, $iPictureId, '', $strFilterFlags);
        }

        // left-right orientation
        if ($spgm_cfg['conf']['galleryOrientation'] == ORIENTATION_LEFTRIGHT)
        {
            print '  <td class="' . CLASS_TD_ORIENTATION_RIGHT . '">' . "\n\n";
        }

        // Prepare layout for stuff left
        print '<br /><br />' . "\n";
        print '<table cellspacing="0" class="' . CLASS_TABLE_PICTURE . '">' . "\n";

        // display the previous/next arrow section if we are not in slideshow mode
        if (!$bSlideshowMode)
        {
            print ' <tr>' . "\n";
            print '	<td class="' . CLASS_TD_PICTURE_NAVI . '"><a id="' . ID_PICTURE_NAVI . '"></a>' . "\n";

            if ($iPreviousPictureId >= 0)
            {
                print spgm_BuildLink($spgm_cfg['theme']['previousPictureIcon'], 'h', ANCHOR_PICTURE, $strGalleryId, -1, $iPreviousPictureId, str_replace(PARAM_VALUE_FILTER_SLIDESHOW, '', $strFilterFlags));
            }
            //multi-language support
            $spgm_cfg['locale']['pictureNaviBar'] = str_replace(PATTERN_CURRENT_PIC, "$iNextPictureId", $spgm_cfg['locale']['pictureNaviBar']);
            $spgm_cfg['locale']['pictureNaviBar'] = str_replace(PATTERN_NB_PICS, "$iPictureNumber", $spgm_cfg['locale']['pictureNaviBar']);
            print ' ' . $spgm_cfg['locale']['pictureNaviBar'] . ' ';

            if ($iNextPictureId < $iPictureNumber)
            {
                print spgm_BuildLink($spgm_cfg['theme']['nextPictureIcon'], 'h', ANCHOR_PICTURE, $strGalleryId, -1, $iNextPictureId, str_replace(PARAM_VALUE_FILTER_SLIDESHOW, '', $strFilterFlags));
            }
            print '  </td>' . "\n" . ' </tr>' . "\n";
        }

        // Client side zoom buttons
        if (count($spgm_cfg['conf']['zoomFactors']) > 0)
        {
            print '</tr>' . "\n" . '<tr>' . "\n" . '	<td class="' . CLASS_TD_ZOOM_FACTORS . '">' . "\n";
            for ($i = 0; $i < count($spgm_cfg['conf']['zoomFactors']); $i++)
            {
                $iHeight = (int) ($arrPictureDim[1] * $spgm_cfg['conf']['zoomFactors'][$i] / 100);
                $iWidth  = (int) ($arrPictureDim[0] * $spgm_cfg['conf']['zoomFactors'][$i] / 100);
                print '<input type="button" class="' . CLASS_BUTTON_ZOOM_FACTORS . '" value=" ' . $spgm_cfg['conf']['zoomFactors'][$i] . '% " ';
                print 'onClick="document.getElementById(' . "'" . ID_PICTURE . "'" . ').setAttribute(' . "'" . 'height' . "'" . ', ' . $iHeight . '); ';
                print 'document.getElementById(' . "'" . ID_PICTURE . "'" . ').setAttribute(' . "'" . 'width' . "'" . ', ' . $iWidth . '); ';
                print 'document.getElementById(' . "'" . ID_PICTURE_NAVI . "'" . ').scrollIntoView()">' . "\n";
            }
            print "\n" . '	</td>' . "\n" . '</tr>' . "\n";
        }

        // EXIF data
        if (count($spgm_cfg['conf']['exifInfo']) > 0)
        {
            if (extension_loaded('exif')) // ... where available
            {
                print '<tr><td>' . "\n";
                $strExifData = spgm_LoadExif($strPictureURL);
                print '[<span onmouseover="return overlib(\'' . $strExifData . '\', CAPTION, \'' . $spgm_cfg['locale']['exifHeading'] . ' ' . $strPictureFilename . '\', STICKY)" onmouseout="return nd()" style="color: #2e408d; font-weight: bold; font-size: 9pt">Exif</span>]';
                print '</td></tr>' . "\n";
            }
        }

        // Load pictures if slideshow mode is enabled
        if ($bSlideshowMode)
        {
            print '<script language="Javascript">' . "\n";
            $iPictureNumber  = count($arrPictureFilenames);
            $_dim            = array();
            $_strPicturePath = '';
            for ($i = 0; $i < $iPictureNumber; $i++)
            {
                $_strPicturePath    = $strPathToPictures . $arrPictureFilenames[$i];
                $_dim               = getimagesize($_strPicturePath);
                $_strPictureCaption = '';
                if (isset($spgm_cfg['captions'][$arrPictureFilenames[$i]]))
                {
                    $_strPictureCaption = $spgm_cfg['captions'][$arrPictureFilenames[$i]];
                }
                print '	 addPicture(\'' . $_strPicturePath . '\', \'' . addslashes($_strPictureCaption) . '\', ' . $_dim[0] . ', ' . $_dim[1] . ');' . "\n";
            }
            print '</script>' . "\n";
        }

        // compute image dimensions
        $iWidth  = $arrPictureDim[0];
        $iHeight = $arrPictureDim[1];
        if ($spgm_cfg['conf']['fullPictureWidth'] != ORIGINAL_SIZE)
        {
            $iWidth = $spgm_cfg['conf']['fullPictureWidth'];
            if ($spgm_cfg['conf']['fullPictureHeight'] == ORIGINAL_SIZE)
            {
                $iHeight = (int) $arrPictureDim[1] * ($spgm_cfg['conf']['fullPictureWidth'] / $arrPictureDim[0]);
            }
            else
            {
                $iHeight = $spgm_cfg['conf']['fullPictureHeight'];
            }
        }
        else
        {
            if ($spgm_cfg['conf']['fullPictureHeight'] != ORIGINAL_SIZE)
            {
                $iHeight = $spgm_cfg['conf']['fullPictureHeight'];
                $iWidth  = (int) $arrPictureDim[0] * ($spgm_cfg['conf']['fullPictureHeight'] / $arrPictureDim[1]);
            }
        }

        // Eventually display the picture
        print '<tr>' . "\n";
        print '	 <td class="' . CLASS_TD_PICTURE_PIC . '">' . "\n";

        // Overlib hidden span for EXIF data
        print '	 <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000"></div>' . "\n";

        spgm_DropShadowsBeginWrap();

        $strHtmlPicture = '<img id="' . ID_PICTURE . '" src="' . $strPictureURL . '" width="' . $iWidth . '" height="' . $iHeight . '"';
        $strHtmlPicture .= ' alt="' . $strPictureURL . '" class="' . CLASS_IMG_PICTURE . '" />';

        if (!($iNextPictureId < $iPictureNumber))
            $iNextPictureId = 0; // to link to the appropriate next pic
        if (!$bSlideshowMode)
        {
            if ($spgm_cfg['conf']['popupOverFullPictures'] == true)
            {
                $iPopupWidth    = $spgm_cfg['conf']['popupWidth'];
                $iPopupHeight   = $spgm_cfg['conf']['popupHeight'];
                $strJustPicture = 'false';
                if ($spgm_cfg['conf']['popupFitPicture'] == true)
                {
                    $iPopupWidth    = $arrPictureDim[0];
                    $iPopupHeight   = $arrPictureDim[1];
                    $strJustPicture = 'true';
                }
                print '		 <a id="spgmPicture" target="_BLANK" href="' . $strPictureURL . '">';
                print $strHtmlPicture;
                print '</a>' . "\n";
            }
            else
            {
                print spgm_BuildLink($strHtmlPicture, '', ANCHOR_PICTURE, $strGalleryId, -1, $iNextPictureId, str_replace(PARAM_VALUE_FILTER_SLIDESHOW, '', $strFilterFlags));
            }
        }
        else
        {
            print $strHtmlPicture;
        }

        spgm_DropShadowsEndWrap();

        print '	 </td>' . "\n";
        print '</tr>' . "\n";

        $file = base64_encode(file_get_contents("" . BASE_DIR . "/" . $strPictureURL . ""));
        // display the picture's filename if needed
        if ($spgm_cfg['conf']['filenameWithPictures'] == true)
        {
            print '<tr>' . "\n";
            print '  <td class="' . CLASS_TD_PICTURE_FILENAME . '">' . "\n";
            echo '<span class="left"><a href="javascript:void(0)" onclick="confirmation(\'' . addslashes($strPictureURL) . '\',\'screenshot\')" title="Delete screenshot"><div class="delete_button" style="position:relative;left:-6px;top:0"><img src="/style/img/delete.png" alt="Delete" /></div></a></span>' . "\n";
            print $strPictureBasename . '' . $_strFileExtension . '';

            $imgurfile = "" . $_SERVER["DOCUMENT_ROOT"] . "/screenshots/Imgur/" . urldecode($strPictureBasename) . ".txt";
            if (!file_exists($imgurfile))
            {
                print '<span id="uploaded" style="float:right"><a href="javascript:void(0)" onclick="imgurUpload(\'' . addslashes($file) . '\', \'' . addslashes($strPictureBasename) . '\')"><img src="/style/img/upload.png" alt="upload" />&nbsp;Upload to Imgur</a></span><br />' . "\n";
            }
            else
            {
                $imgur_url = file_get_contents($imgurfile);
                print '<span id="uploaded" style="float:right"><a href="' . $imgur_url . '">Link to your image on imgur.com</a><img src="/style/img/external_link.png" style="margin-bottom:3px;margin-left:6px" alt="ext" /></span><br />' . "\n";
            }

            print ' </td>' . "\n";
            print '</tr>' . "\n";
        }

        // display the caption
        print '<tr>' . "\n";
        print '	 <td id="' . ID_PICTURE_CAPTION . '" class="' . CLASS_TD_PICTURE_CAPTION . '">&nbsp;' . "\n";
        if (isset($spgm_cfg['captions'][$strPictureFilename]))
        {
            print $spgm_cfg['captions'][$strPictureFilename];
        }

        print '	 </td>' . "\n";
        print '</tr>' . "\n";
        print '</table>' . "\n";

        // left-right orientation
        if ($spgm_cfg['conf']['galleryOrientation'] == ORIENTATION_LEFTRIGHT)
        {
            print '  </td>' . "\n";
            print '</tr>' . "\n";
            print '</table>' . "\n";
        }

        if ($bSlideshowMode)
        {
            print '<script language="Javascript">runSlideShow();</script>' . "\n";
        }

    }
    else
        spgm_Error(ERRMSG_UNKNOWN_PICTURE);

}

################################################################################

function spgm_DisplayGallery($strGalleryId, $iPageIndex, $strFilterFlags)
{
    spgm_Trace('<p>function spgm_DisplayGallery</p>' . "\n" . 'strGalleryId: ' . $strGalleryId . '<br />' . "\n" . 'iPageIndex: ' . $iPageIndex . '<br />' . "\n" . 'strFilterFlags: ' . $strFilterFlags . '<br />' . "\n");


    if (!spgm_IsGallery($strGalleryId))
        spgm_Error(ERRMSG_UNKNOWN_GALLERY);
    else
    {
        $arrPictureFilenames = spgm_CreatePictureArray($strGalleryId, $strFilterFlags, true);
        if ($iPageIndex == '')
            $iPageIndex = 1;
        spgm_DisplayGalleryNavibar($strGalleryId, $strFilterFlags, '', $arrPictureFilenames);
        // display sub-galleries in a hierarchical manner
        spgm_DisplayGalleryHierarchy($strGalleryId, 1, $strFilterFlags);
        if (count($arrPictureFilenames) > 0)
            spgm_DisplayThumbnails($strGalleryId, $arrPictureFilenames, '', $iPageIndex, $strFilterFlags);
        // extra vertical padding before displaying the subgalleries
        print '<br />' . "\n\n";
    }
}


################################################################################

function spgm_DisplayThumbnails($strGalleryId, $arrPictureFilenames, $iPictureId, $iPageIndex, $strFilterFlags)
{
    global $spgm_cfg;

    $strPathToPictures = DIR_GAL . $strGalleryId . '/';
    $iPictureNumber    = count($arrPictureFilenames);
    $iPageNumber       = $iPictureNumber / $spgm_cfg['conf']['thumbnailsPerPage'];
    if ($iPageNumber > (int) ($iPictureNumber / $spgm_cfg['conf']['thumbnailsPerPage']))
        $iPageNumber = (int) ++$iPageNumber;
    if (!isset($iPageIndex))
    {
        $iPictureOffsetStart = 0;
        $iPageFrom           = 1;
    }
    else
    {
        if (($iPageIndex == '') || ($iPageIndex < 1) || ($iPageIndex > $iPageNumber))
            $iPageIndex = 1;
    }

    if ($iPictureId == '')
        $iPictureId = -1; // so picture information are not highlighted
    else
        $iPageIndex = ((int) ($iPictureId / $spgm_cfg['conf']['thumbnailsPerPage'])) + 1;

    $iPictureOffsetStart = ($iPageIndex - 1) * $spgm_cfg['conf']['thumbnailsPerPage'];
    $iPictureOffsetStop  = $iPictureOffsetStart + $spgm_cfg['conf']['thumbnailsPerPage'];
    if ($iPictureOffsetStop > $iPictureNumber)
        $iPictureOffsetStop = $iPictureNumber;
    $iPageFrom = $iPageIndex;

    spgm_Trace('<p>function spgm_DisplayThumbnails</p>' . "\n" . 'strPathToPictures: ' . $strPathToPictures . '<br />' . "\n" . 'iPictureNumber: ' . $iPictureNumber . '<br />' . "\n" . 'iPictureId: ' . $iPictureId . '<br />' . "\n" . 'iPictureOffsetStart: ' . $iPictureOffsetStart . '<br />' . "\n" . 'iPictureOffsetStop: ' . $iPictureOffsetStop . '<br />' . "\n" . 'iPageFrom: ' . $iPageFrom . '<br />' . "\n" . 'iPageNumber: ' . $iPageNumber . '<br />' . "\n" . 'iPageIndex: ' . $iPageIndex . '<br />' . "\n");


    // left-right orientation
    if ($spgm_cfg['conf']['galleryOrientation'] == ORIENTATION_LEFTRIGHT AND $iPictureId != -1)
    {
        print '<table class="' . CLASS_TABLE_ORIENTATION . '">' . "\n";
        print '<tr>' . "\n";
        print '	 <td class="' . CLASS_TD_ORIENTATION_LEFT . '">' . "\n\n";
    }


    print '<table cellpadding="0" cellspacing="0" class="' . CLASS_TABLE_THUMBNAILS . '">' . "\n";
    print '<tr>' . "\n";

    $iItemCounter = 0;

    for ($i = $iPictureOffsetStart; $i < $iPictureOffsetStop; $i++)
    {
        $strPictureFilename   = $arrPictureFilenames[$i];
        $_strFileExtension    = strrchr($strPictureFilename, '.');
        $strPictureBasename   = substr($strPictureFilename, 0, -strlen($_strFileExtension));
        $strPictureURL        = $strPathToPictures . $strPictureFilename;
        $strThumbnailFilename = PREF_THUMB . $arrPictureFilenames[$i];
        if (defined('DIR_THUMBS'))
        {
            $strThumbnailFilename = DIR_THUMBS . PREF_THUMB . $arrPictureFilenames[$i];
        }
        $strThumbnailURL        = $strPathToPictures . $strThumbnailFilename;
        $arrThumbnailDim        = getimagesize($strThumbnailURL);
        $iCurrentPictureIndex   = $i + 1; // index that is displayed
        $strClassThumbnailThumb = CLASS_TD_THUMBNAILS_THUMB;
        $strClassImgThumbnail   = CLASS_IMG_THUMBNAIL;
        if ($i == $iPictureId)
        {
            $strClassThumbnailThumb = CLASS_TD_THUMBNAILS_THUMB_SELECTED;
            $strClassImgThumbnail   = CLASS_IMG_THUMBNAIL_SELECTED;
        }


        // new line
        if (($iItemCounter++ % $spgm_cfg['conf']['thumbnailsPerRow']) == 0)
            if ($iItemCounter > 1)
                print '</tr>' . "\n" . '<tr>' . "\n"; // test for HTML 4.01 compatibility

        // TD opening for XHTML compliance when MODE_TRACE is on
        // TODO: valign=top does not work when new pictures reside amongst old ones
        print '	 <td style="vertical-align:top" class="' . $strClassThumbnailThumb . '">' . "\n";
        // ...

        if (spgm_IsNew($strPictureURL) && !strstr($strFilterFlags, PARAM_VALUE_FILTER_NEW))
        {
            if ($spgm_cfg['theme']['newItemIcon'] != '')
            {
                $strHtmlNew = $spgm_cfg['theme']['newItemIcon'] . '<br />' . "\n";
            }
            else
            {
                $strHtmlNew = '<center><span style="color: #ffd600">' . $spgm_cfg['locale']['filterNew'];
                $strHtmlNew .= '</span></center>' . "\n";
            }
        }
        else
            $strHtmlNew = '';

        $arrPictureDim = getimagesize($strPictureURL);

        // ...
        print '	 ' . $strHtmlNew . "\n";

        spgm_DropShadowsBeginWrap();

        $strHtmlThumbnail = '<img src="' . $strThumbnailURL . '" width="' . $arrThumbnailDim[0] . '"';
        $strHtmlThumbnail .= ' height="' . $arrThumbnailDim[1] . '" alt="' . $strThumbnailURL;
        $strHtmlThumbnail .= '" class="' . $strClassImgThumbnail . '" />';

        if ($spgm_cfg['conf']['popupPictures'])
        {
            if (!strstr($strFilterFlags, PARAM_VALUE_FILTER_NOTHUMBS))
            {
                $strFilterFlags .= PARAM_VALUE_FILTER_NOTHUMBS;
            }

            $iWidth  = $spgm_cfg['conf']['popupWidth'];
            $iHeight = $spgm_cfg['conf']['popupHeight'];
            $strURL  = $spgm_cfg['global']['documentSelf'] . '?' . PARAM_NAME_GALID . '=' . $strGalleryId . '&amp;' . PARAM_NAME_PICID . '=' . $i . '&amp;' . PARAM_NAME_FILTER . '=' . str_replace(PARAM_VALUE_FILTER_SLIDESHOW, '', $strFilterFlags) . $spgm_cfg['global']['URLExtraParams'] . '#' . ANCHOR_PICTURE;
            ;

            $strJustPicture = 'false';

            if ($spgm_cfg['conf']['popupFitPicture'] == true)
            {
                $iWidth         = $arrPictureDim[0];
                $iHeight        = $arrPictureDim[1];
                $strURL         = $strPictureURL;
                $strJustPicture = 'true';
            }

            print '	 <a href="#?" onclick="popupPicture(\'' . $strURL . '\', ' . $iWidth . ', ' . $iHeight . ', ' . $strJustPicture . ')">';
            print $strHtmlThumbnail;
            print '</a>' . "\n";

        }
        else
        {
            print '	 ' . spgm_BuildLink($strHtmlThumbnail, 'yui3-pjax', ANCHOR_PICTURE, $strGalleryId, -1, $i, str_replace(PARAM_VALUE_FILTER_SLIDESHOW, '', $strFilterFlags));
        }

        spgm_DropShadowsEndWrap();

        print '<br />' . "\n";

        // display picture extra information if wanted
        if ($spgm_cfg['conf']['filenameWithThumbnails'] == true)
        {
            print $strPictureBasename . '<br />';
        }
        if ($spgm_cfg['conf']['pictureInfoedThumbnails'] == true)
        {
            $picsize = (int) (filesize($strPictureURL) / 1024);
            print '  [ ' . $arrPictureDim[0] . 'x' . $arrPictureDim[1] . ' - ' . $picsize . ' KB ]' . "\n";
        }

        // display caption along with the thumbnail
        if ($spgm_cfg['conf']['captionedThumbnails'] == true)
        {
            if (isset($spgm_cfg['captions'][PREF_THUMB . $strPictureFilename]))
            {
                print '		 <div class="' . CLASS_DIV_THUMBNAILS_CAPTION . '">';
                print $spgm_cfg['captions'][PREF_THUMB . $strPictureFilename];
                print '</div>' . "\n";
            }
            elseif ($spgm_cfg['conf']['pictureCaptionedThumbnails'])
            {
                if (isset($spgm_cfg['captions'][$strPictureFilename]))
                {
                    print "\n" . '	<div class="' . CLASS_DIV_THUMBNAILS_CAPTION . '">';
                    print $spgm_cfg['captions'][$strPictureFilename];
                    print '</div>' . "\n";
                }
            }
        }

        print '	 </td>' . "\n";
    }

    // navi bar generation
    if ($iPictureNumber > 0)
    {
        print '</tr>' . "\n";
        print '<tr>' . "\n";
        print '	 <td colspan="' . $spgm_cfg['conf']['thumbnailsPerRow'] . '" class="' . CLASS_TD_THUMBNAILS_NAVI . '">';
        // display "thumbnail navi" if all the thumbs are not displayed on the same page
        spgm_DisplayThumbnailNavibar($iPageIndex, $iPageNumber, $strGalleryId, $strFilterFlags);

        // toggles
        $galleryInfo = spgm_GetGalleryInfo($strGalleryId, $arrPictureFilenames);
        spgm_DisplayFilterToggles($strGalleryId, $strFilterFlags, $galleryInfo);
    }

    // for HTML 4.01 compatibility ...
    // if there are no thumbnails, then format the <td> markup correctly
    if ($iItemCounter == 0)
        print '  <td>' . "\n";

    print '  </td>' . "\n";
    print '</tr>' . "\n";
    print '</table>' . "\n";

    // left-right orientation
    if ($spgm_cfg['conf']['galleryOrientation'] == ORIENTATION_LEFTRIGHT AND $iPictureId != -1)
    {
        print "\n" . '  </td>' . "\n";
    }
}

#############
# Main
#############

$strParamGalleryId   = '';
$strParamPictureId   = '';
$strParamPageIndex   = '';
$strParamFilterFlags = '';

// extract URL parameters
if (ini_get('register_globals') == '1')
{
    $spgm_cfg['global']['documentSelf'] = basename($PHP_SELF);
    if (isset($$strVarGalleryId))
    {
        $strParamGalleryId = $$strVarGalleryId;
    }
    if (isset($$strVarPictureId))
    {
        $strParamPictureId = $$strVarPictureId;
    }
    if (isset($$strVarPageIndex))
    {
        $strParamPageIndex = $$strVarPageIndex;
    }
    if (isset($$strVarFilterFlags))
    {
        $strParamFilterFlags                    = $$strVarFilterFlags;
        $spgm_cfg['global']['propagateFilters'] = true;
    }
}
else
{
    $spgm_cfg['global']['documentSelf'] = basename($_SERVER['PHP_SELF']);
    if (isset($_GET[PARAM_NAME_GALID]))
        $strParamGalleryId = $_GET[PARAM_NAME_GALID];
    if (isset($_GET[PARAM_NAME_PICID]))
        $strParamPictureId = $_GET[PARAM_NAME_PICID];
    if (isset($_GET[PARAM_NAME_PAGE]))
        $strParamPageIndex = $_GET[PARAM_NAME_PAGE];
    if (isset($_GET[PARAM_NAME_FILTER]))
    {
        $strParamFilterFlags                    = $_GET[PARAM_NAME_FILTER];
        $spgm_cfg['global']['propagateFilters'] = true;
    }
    // Auto-template mode (available for register_globals = false only)
    if (isset($_GET))
    {
        foreach ($_GET as $key => $value)
        {
            if (substr($key, 0, strlen(PARAM_PREFIX)) != PARAM_PREFIX)
            {
                $spgm_cfg['global']['URLExtraParams'] .= '&amp;' . $key . '=' . $value;
            }
        }
    }
}


// load external resources
spgm_LoadConfig($strParamGalleryId);
spgm_LoadPictureCaptions($strParamGalleryId);

// User filter initialization
if ($spgm_cfg['conf']['filters'] != '')
{
    if (!$spgm_cfg['global']['propagateFilters'])
    {
        if (strstr($spgm_cfg['conf']['filters'], PARAM_VALUE_FILTER_NOTHUMBS) && !strstr($strParamFilterFlags, PARAM_VALUE_FILTER_NOTHUMBS))
            $strParamFilterFlags .= PARAM_VALUE_FILTER_NOTHUMBS;
        if (strstr($spgm_cfg['conf']['filters'], PARAM_VALUE_FILTER_NEW) && !strstr($strParamFilterFlags, PARAM_VALUE_FILTER_NEW))
            $strParamFilterFlags .= PARAM_VALUE_FILTER_NEW;
    }
}


print "\n\n" . '<!-- begin table wrapper -->' . "\n";
print '<a></a>' . "\n";
print '<table class="' . CLASS_TABLE_WRAPPER . '">' . "\n" . ' <tr>' . "\n";

if ($strParamGalleryId == '')
{
    // the gallery is not specified -> generate the gallery "tree"
    spgm_DisplayGalleryHierarchy('', 0, $strParamFilterFlags);
}
else
{
    print '  <td>' . "\n";
    if ($strParamPictureId == '')
    {
        // we've got a gallery but no picture -> display thumbnails
        spgm_DisplayGallery($strParamGalleryId, $strParamPageIndex, $strParamFilterFlags);
    }
    else
    {
        spgm_DisplayPicture($strParamGalleryId, $strParamPictureId, $strParamFilterFlags);
    }
    print '	 </td>' . "\n";
}

print ' </tr>' . "\n";

//display the link to SPGM website
print ' <tr>' . "\n" . '  <td colspan="' . $spgm_cfg['conf']['galleryListingCols'] . '" class="' . CLASS_TD_SPGM_LINK . '">' . "\n";
spgm_DispSPGMLink();
print '	 </td>' . "\n" . ' </tr>' . "\n";

print '</table>' . "\n" . '<!-- end table wrapper -->' . "\n\n";
