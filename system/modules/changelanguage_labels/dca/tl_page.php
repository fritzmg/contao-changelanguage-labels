<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  inspiredminds.at 2016
 * @author     Fritz Michael Gschwantner <fmg@inspiredminds.at>
 * @package    changelanguage_labels
 */


$GLOBALS['TL_DCA']['tl_page']['list']['label']['label_callback'] = array('tl_page_labels', 'languageLabels');

class tl_page_labels
{
	public function languageLabels($row, $label, $dc=null, $imageAttribute='', $blnReturnImage=false, $blnProtected=false)
	{
		// generate the default label
		$objDcaClass = null;
		if (in_array('cacheicon', ModuleLoader::getActive()))
			$objDcaClass = new tl_page_cacheicon();
		elseif (in_array('Avisota', ModuleLoader::getActive()))
			$objDcaClass = new tl_page_avisota();
		else
			$objDcaClass = new tl_page();
		$label = $objDcaClass->addIcon($row, $label, $dc, $imageAttribute, $blnReturnImage, $blnProtected);

		// return the label for root or folder page
		if( $row['type'] == 'root' || $row['type'] == 'folder' )
			return $label;

		// load the current page
		$objPage = PageModel::findWithDetails( $row['id'] );

		// prepare alternate pages
		$objAlternates = null;

		if( $objPage->languageMain )
		{
			// get all pages referencing the same fallback page
			$t = \PageModel::getTable();
			$objAlternates = PageModel::findBy( array("$t.languageMain = ? OR $t.id = ?"), array( $objPage->languageMain, $objPage->languageMain ) );
		}
		else
		{
			// get all pages referencing the current page as its fallback
			$objAlternates = PageModel::findByLanguageMain( $objPage->id );
		}

		// check if alternates were found
		if( $objAlternates !== null )
		{
			$label.= '<ul class="tl_page_language_alternates">';

			// go through each page and add link
			while( $objAlternates->next() )
			{
				if( $objAlternates->id == $objPage->id )
					continue;

				$objAlternates->current()->loadDetails();
				$label .= '<li><a href="contao/main.php?do=page&amp;node=' . $objAlternates->id . '&amp;ref=' . TL_REFERER_ID . '">' . $objAlternates->language . '</a></li>';
			}

			$label.= '</ul>';
		}

		return $label;
	}
}
