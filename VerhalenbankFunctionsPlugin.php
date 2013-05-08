<?php
if (!defined('VERHALENBANKFUNCTIONS_PLUGIN_DIR')) {
    define('VERHALENBANKFUNCTIONS_PLUGIN_DIR', dirname(__FILE__));
}
if (!defined('VERHALENBANKFUNCTIONS_IMAGE_DIR')) {
    define('VERHALENBANKFUNCTIONS_IMAGE_DIR', dirname(__FILE__) . "/views/shared/images");
}

require_once VERHALENBANKFUNCTIONS_PLUGIN_DIR . '/helpers/ElementFunctions.php';
require_once VERHALENBANKFUNCTIONS_PLUGIN_DIR . '/admin_functions.php';
require_once VERHALENBANKFUNCTIONS_PLUGIN_DIR . '/public_functions.php';


class VerhalenbankFunctionsPlugin extends Omeka_Plugin_AbstractPlugin
{
	protected $_hooks = array('install',
	                            'public_head',
                                'config_form',
                                'config',
#                                'public_items_show_top',
                                'public_items_show_sidebar_top',
                                'public_items_show_sidebar_ultimate_top',
                                'admin_head',
#	                            'admin_items_show',
                                'admin_items_show_sidebar',
                                'initialize');
	
	protected $_filters = array('display_elements',
	                            'file_markup',
	                            'item_citation',
	                            'public_navigation_admin_bar',
#                                'admin_navigation_main',
#                                'public_navigation_main'
                                );

    public $_metadata_public_hide = array("Dublin Core" => array("Contributor", "Rights", "Creator"), #CREATOR TEMPORARY
                                            "Item Type Metadata" => array("Extreme", "Kloeke Georeference", "Entry date"));
    
    public $_metadata_to_the_right = array("Dublin Core" => array("Creator", "Contributor", "Type", "Language"),
                                            "Item Type Metadata" => array("Collector"));


    public function hookConfigForm()
    {
        // If necessary, upgrade the plugin options
        include 'config_form.php';        
    }

    public function hookConfig($args)
    {
        $post = $args['post'];
        set_option('textcopyrightwarning', $post['textcopyrightwarning']);
        set_option('textextremewarning', $post['textextremewarning']);
        set_option('imagewarning', $post['imagewarning']);
        set_option('kloekelink', $post['kloekelink']);
        set_option('subcollectionswithtypes', $post['subcollectionswithtypes']);
        set_option('mediumsearchablefields', $post['mediumsearchablefields']);
        set_option('mediumsearchstyle', $post['mediumsearchstyle']);
    }
    
    public function hookInstall(){
        set_option('textcopyrightwarning', 
            "<p style = 'color:red'><b>Auteursrecht:</b></p> 
De tekst bevat auteursrechtelijk beschermde informatie. 
De inhoud is daarom afgeschermd, en kan alleen worden geraadpleegd op het Meertens Instituut.
<br>
This text contains copyrighted information.");

        set_option('textextremewarning', 
            "<p style = 'color:red'><b>Extreme:</b></p> 
Dit record bevat extreme elementen van enigerlei aard (racisme, sexisme, schuttingtaal, godslastering, expliciete naaktheid, majesteitsschennis). 
De inhoud is daarom afgeschermd, en kan alleen worden geraadpleegd op het Meertens Instituut.
<br>
This text contains language that can be perceived as extreme.");

        set_option('imagewarning', 
            "<p style = 'color:red'>Waarschuwing: </p>Dit bestand bevat auteursrechtelijke informatie of bevat extreme facetten. <br>
De inhoud is daarom afgeschermd, en kan alleen worden geraadpleegd op het Meertens Instituut, of met een admin account.");

        set_option('kloekelink', 'http://www.meertens.knaw.nl/kaart/v3/rest/?type=dutchlanguagearea&data[]=');
        set_option('subcollectionswithtypes', "2,6,7");
        set_option('mediumsearchablefields', "43,49,50,60,44,48,39,40,61,58,52,41,63,66,93,65,53,67,51,1");
        set_option('mediumsearchstyle', "contains");
    }

    /**
     * Initialize the plugin.
     */
    public function hookInitialize(){
        add_translation_source(dirname(__FILE__) . '/languages');
    }

	/**
	*   admin_items_show_sidebar
	*   information about the contributor is added on the botton of the sidebar panel
	**/
    public function hookAdminItemsShowSidebar($args){
        contributor_information_tab_admin($args);
	}


    public function filterItemCitation($citation, $args) {
        $citation = "None";
        return $citation;
    }

    /**
    *   filterDisplayElements:
    *   Here we filter the elements based on the variables $_metadata_public_hide and $_metadata_to_the_right
    *   
    *               To do: NOT IN ADMIN MODE
    **/
    public function filterDisplayElements($elementSets) {
        if (!is_admin_theme()) { #only in the public view!
            //here we take out the elements that will be shown on the div on the metadata div
            foreach($elementSets as $setName=>$elements) {
                foreach($elements as $element) {
                    foreach($this->_metadata_to_the_right as $sn=>$el){
                        if(in_array($element->name, $el)){
                            unset($elementSets[$setName][$element->name]);
                        }
                    }
                }
            }
            if ($user = current_user()){ #don't filter this stuff out when logged in
                return $elementSets;
            }
            //here we filter the elements that should not be seen by the public
            foreach($elementSets as $setName=>$elements) {
                foreach($elements as $element) {
                    foreach($this->_metadata_public_hide as $sn=>$el){ #omitting the element set names
                        if(in_array($element->name, $el)){
                            unset($elementSets[$setName][$element->name]);
                        }
                    }
                }
            }
            return $elementSets;
        }
        return $elementSets;
    }

    public function hookPublicItemsShowSidebarTop($args){
        $_metadata_fields_public_hide = array_merge($this->_metadata_public_hide["Dublin Core"], $this->_metadata_public_hide["Item Type Metadata"]);
        $item = $args['item'];
        print '<div id="item-metadata" class="element">';
        print '<h2>Metadata</h2>';
        foreach($this->_metadata_to_the_right as $setName=>$elements) {
            foreach($elements as $element) {
                if (!in_array($element, $_metadata_fields_public_hide) || $user = current_user()){ //to check if it is allowed to show the item publicly AND if a user is logged in
                    if (strlen(metadata('item', array($setName, $element))) > 0){ // don't show when empty
                        print '<div id="" class="element">';
                        print "<h3>" . __($element) . " </h3>";
                        print '<div class="element-text">' . metadata('item', array($setName, $element)) . "</div>";
                        print '</div>';
                    }
                }
            }
        }
        print "</div>";
    }

    public function hookPublicItemsShowSidebarUltimateTop($args){
        print "<ul class='slide-toggle'>";
        print '<li class="up" id="slidetoggle">'.__("Show browse links").'</li>'; #TRANSLATE Informatie uitklappen
        print '<li class="down" id="slidetoggle" style="display:none;">'.__("Hide browse links").'</li>';
        print "</ul>";
        
    }
    
    public function filterFileMarkup($html, $args){
        if ($user = current_user()){
    		return $html;
    	}
        $file = $args['file'];
        if((metadata($file, array('Dublin Core', 'Rights')) == 'nee') || (metadata($file, array('Dublin Core', 'Relation')) == 'ja')){
            return get_option('imagewarning'); 
            /*'<p>Een bestand bevat auteursrechtelijke informatie of bevat extreme facetten. 
            <br>De inhoud is daarom afgeschermd, en kan alleen worden geraadpleegd op het Meertens Instituut, of met een admin account.<br><br></p>';*/
        }
        return $html;
    }

    /**
     * Add the data linker navigation link.
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array('label' => __('Data linker'), 'uri' => url('data-linker'));
        return $nav;
    }

    /**
     * Add the data linker navigation link.
     */
    public function hookPublicHead($args)
    {
        clear_filters(array('Display', 'Item', 'Dublin Core', 'Title'));
        queue_css_file('linked'); // assumes myplugin has a /views/public/css/linked.css file
        queue_js_file('showHide');
        $view = get_view();
        if(isset($view->item)) {
            if (metadata("item", 'Item Type Name') == "Volksverhaaltype"){
                add_filter(array('Display', 'Item', 'Dublin Core', 'Identifier'),                   'identifier_info_retrieve_popup_jquery', 7);
            }
            add_filter(array('Display', 'Item', 'Dublin Core', 'Subject'),                      'subject_info_retrieve_popup_jquery', 7);
            add_filter(array('Display', 'Item', 'Dublin Core', 'Language'),                     'language_info_retrieve_popup_jquery', 7);
            add_filter(array('Display', 'Item', 'Dublin Core', 'Type'),                         'type_info_retrieve_popup_jquery', 7);
            add_filter(array('Display', 'Item', 'Dublin Core', 'Creator'),                      'creator_info_retrieve_popup_jquery', 7);
            add_filter(array('Display', 'Item', 'Item Type Metadata', 'Collector'),             'collector_info_retrieve_popup_jquery', 7);
            add_filter(array('Display', 'Item', 'Item Type Metadata', 'Subgenre'),              'subgenre_info_retrieve_popup_jquery', 7);

            add_filter(array('Display', 'Item', 'Dublin Core', 'Description'),                  'scroll_to_full_text', 5);
            add_filter(array('Display', 'Item', 'Item Type Metadata', 'Text'),                  'text_extreme_hide', 5);
            add_filter(array('Display', 'Item', 'Item Type Metadata', 'Text'),                  'text_copyright_hide', 6);
            add_filter(array('Display', 'Item', 'Dublin Core', 'Source'),                       'make_urls_clickable_in_text');
            add_filter(array('Display', 'Item', 'Item Type Metadata', 'Kloeke georeference'),   'my_kloeke_link_function', 4);
            add_filter(array('Display', 'Item', 'Dublin Core', 'Date'),                         'present_dates_as_language', 20);
        }    
    }

    public function hookAdminHead($args)
    {
        $view = get_view();
        if(isset($view->item)) {
            if (metadata("item", 'Item Type Name') == "Volksverhaaltype"){
                add_filter(array('Display', 'Item', 'Dublin Core', 'Identifier'),                   'identifier_info_retrieve_popup_jquery', 7);
            }
            add_filter(array('Display', 'Item', 'Dublin Core', 'Subject'),                      'subject_info_retrieve_popup_jquery', 7);
            add_filter(array('Display', 'Item', 'Dublin Core', 'Language'),                     'language_info_retrieve_popup_jquery', 7);
            add_filter(array('Display', 'Item', 'Dublin Core', 'Type'),                         'type_info_retrieve_popup_jquery', 7);
            add_filter(array('Display', 'Item', 'Dublin Core', 'Creator'),                      'creator_info_retrieve_popup_jquery', 7);
            add_filter(array('Display', 'Item', 'Item Type Metadata', 'Collector'),             'collector_info_retrieve_popup_jquery', 7);
            add_filter(array('Display', 'Item', 'Item Type Metadata', 'Subgenre'),              'subgenre_info_retrieve_popup_jquery', 7);

            add_filter(array('Display', 'Item', 'Dublin Core', 'Description'),                  'scroll_to_full_text');
            add_filter(array('Display', 'Item', 'Item Type Metadata', 'Text'),                  'text_extreme_hide', 5);
            add_filter(array('Display', 'Item', 'Item Type Metadata', 'Text'),                  'text_copyright_hide', 6);
            add_filter(array('Display', 'Item', 'Dublin Core', 'Source'),                       'make_urls_clickable_in_text');
            add_filter(array('Display', 'Item', 'Item Type Metadata', 'Kloeke georeference'),   'my_kloeke_link_function', 4);
            add_filter(array('Display', 'Item', 'Dublin Core', 'Date'),                         'present_dates_as_language', 20);
        }
    }

    public function filterPublicNavigationMain($args){
        #ONLY FOR NAVIGATION
    }
    
    public function filterPublicNavigationAdminBar($navLinks)
    {
        $view = get_view();
        if(isset($view->item)) {
            $record = $view->item;
            $aclRecord = $view->item;
        }

        if(isset($view->collection)) {
            $record = $view->collection;
            $aclRecord = $view->collection;
        }

        if(isset($view->simple_pages_page)) {
            $record = $view->simple_pages_page;
            $aclRecord = 'SimplePages_Page';
        }

        if(isset($view->exhibit_page)) {
            $record = $view->exhibit_page;
            $aclRecord = $view->exhibit;
        }                

        if(!isset($record)) {
            return $navLinks;
        }

        if(is_allowed($aclRecord, 'edit')) {
#            set_theme_base_url('admin');
            if(get_class($record) == 'ExhibitPage') {
                $url = admin_url('exhibits/edit-page-content/' . $record->id);
            } else {
                $url = url('admin/items/edit/' . $record->id);
            }
#            print "<pre>" . url('admin/items/edit/' . $record->id) . "</pre>";
            //want to place it first in the navigation, so do an array merge
            $editLinks['Edit Link'] = array(
                    'label'=>'Edit',
                    'uri'=> $url
                    );
            revert_theme_base_url();
            $navLinks = array_merge($editLinks, $navLinks);
        }
        return $navLinks;
    }
}

#add_filter(array('Display', 'Item', 'Item Type Metadata', 'Comments'), 'dummy_printer', 5);

function dummy_printer($args){
    return "DUMMY";
}

function make_urls_clickable_in_text($args){
    return preg_replace('#(\A|[^=\]\'"a-zA-Z0-9])(http[s]?://(.+?)/[^()<>\s]+)#i', '\\1<a target="linked" href="\\2">\\2</a>', $args);
#	return url_to_link($args);
}

function my_kloeke_link_function($args){
    $kloeke_link = get_option('kloekelink') . $args;
    return "<a href='$kloeke_link'>$args</a>";
}


/**
 * Return the site-wide search form.
 * 
 * @package Omeka\Function\Search
 * @param array $options Valid options are as follows:
 * - show_advanced (bool): whether to show the advanced search; default is false.
 * - submit_value (string): the value of the submit button; default "Submit".
 * - form_attributes (array): an array containing form tag attributes.
 * @return string The search form markup.
 */
function search_form_extended(array $options = array())
{
    return get_view()->searchForm($options);
}


?>