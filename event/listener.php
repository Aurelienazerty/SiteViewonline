<?php
/**
*
* Detailed Viewonline extension for the phpBB Forum Software package.
*
* @copyright (c) 2013 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace Aurelienazerty\SiteViewonline\event;

/**
* Event listener
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/** @var string phpEx */
	protected $php_ext;

	/**
	* Constructor
	*
	* @param \phpbb\config\config                 $config           Config object
	* @param \phpbb\db\driver\driver_interface    $db               DBAL object
	* @param \phpbb\user                          $user             User object
	* @param \phpbb\auth\auth                     $auth             User object
	* @param string                               $phpbb_root_path  phpbb_root_path
	* @param string                               $php_ext          phpEx
	* @return \Aurelienazerty\SiteViewonline\event\listener
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\auth\auth $auth, $phpbb_root_path, $php_ext)
	{
		$this->config = $config;
		$this->user = $user;
		$this->db = $db;
		$this->auth = $auth;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	static public function getSubscribedEvents()
	{
		return array(
		'core.viewonline_overwrite_location'			=> 'site_viewonline',
		);
	}

	public function site_viewonline($event)
	{
		$forum_data = $event['forum_data'];
		$on_page = $event['on_page'];
		$location = $event['location'];
		$location_url = $event['location_url'];
		$row = $event['row'];
		
		/* Si on est sur le site :
				- il faut mettre à jour $event['location_url'] avec l'URL de la page complet
				- il faut mettre à jour $event['location'] avec la description de la page
		*/
		if (strpos($on_page[1], '..') !== false) {
			$location = 'Index du site';
			$location_url = 'https://www.team-azerty.com';
			//Selon $on_page[1]
			if (strpos($on_page[1], 'lan') !== false) {
				$location = 'Rubrique LAN';
				$location_url .= '/html/lan';
			} else if (strpos($on_page[1], 'actu') !== false || strpos($on_page[1], 'news') !== false) {
				$location = 'Rubrique Actualités';
				$location_url .= '/html/news';
			} else if (strpos($on_page[1], 'bench') !== false || strpos($on_page[1], 'config') !== false) {
				$location = 'Rubrique Benchs';
				$location_url .= '/html/benchs';
			} else if (strpos($on_page[1], 'article') !== false) {
				$location = 'Rubrique Articles';
				$location_url .= '/html/articles';
			} else if (strpos($on_page[1], 'foot') !== false) {
				$location = 'Rubrique Prono-foot';
				$location_url .= '/html/prono-foot';
			} else if (strpos($on_page[1], 'rss') !== false) {
				$location = 'Flux RSS';
				$location_url .= '/html/prono-foot';
			} else if (strpos($on_page[1], 'autorisation_image') !== false) {
				$location = 'Consulte une image sur le site';
				$query = $row['session_page'];
				
				preg_match('/&img=(.*)/', $query, $matches);
				$img = $matches[1];
				$img = str_replace('images%2F', '', $img);
				$photo = getPhotoFromUrl($img);
				
				$location_url .= '/images/view-' . $photo->photo_id() . '.html';
			}
			$event['location']= $location;
			$event['location_url'] = $location_url;
		}
	}
}
