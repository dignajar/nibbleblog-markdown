<?php

/*
 * Nibbleblog -
 * http://www.nibbleblog.com
 * Author Diego Najar

 * Last update: 06/11/2012

 * All Nibbleblog code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
*/

class Plugin {

	public $name;
	public $description;
	public $author;
	public $version;
	public $url;
	public $display;

	public $slug_name;

	public $db;

	public $fields;

	public $dir_name;

	function __construct()
	{
		$reflector = new ReflectionClass(get_class($this));
		$this->dir_name = basename(dirname($reflector->getFileName()));

		$this->display = true;
		$this->fields = array();
	}

	public function install()
	{
		if( !mkdir(PATH_PLUGINS_DB.$this->dir_name,0777, true) )
			return(false);

		// Template
		$xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
		$xml .= '<plugin>';
		$xml .= '</plugin>';

		// Object
		$new_obj = new NBXML($xml, 0, FALSE, '', FALSE);

		// Default attributes
		$new_obj->addAttribute('name', $this->name);
		$new_obj->addAttribute('author', $this->author);
		$new_obj->addAttribute('version', $this->version);
		$new_obj->addAttribute('installed_at', Date::unixstamp());

		// Default fields
		$new_obj->addChild('position', 0);
		$new_obj->addChild('title', $this->name);

		foreach($this->fields as $field=>$value)
		{
			$new_obj->addChild($field,$value);
		}

		if( !$new_obj->asXml( PATH_PLUGINS_DB.$this->dir_name.'/db.xml' ) )
			return(false);

		return(true);
	}

	public function uninstall()
	{
		if( unlink( PATH_PLUGINS_DB.$this->dir_name.'/db.xml' ) )
		{
			return( rmdir( PATH_PLUGINS_DB.$this->dir_name ) );
		}

		return(false);
	}

	public function is_installed()
	{
		return( file_exists(PATH_PLUGINS_DB.$this->dir_name.'/db.xml') );
	}

	public function init_db()
	{
		if( $this->is_installed() )
		{
			$this->db = new NBXML(PATH_PLUGINS_DB.$this->dir_name.'/db.xml', 0, TRUE, '', FALSE);

			return(true);
		}

		return(false);
	}

	public function get_field_db($name)
	{
		return( (string) $this->db->getChild($name) );
	}

	// EJ: array( 'first_name'=>'Diego', 'last_name'=>'Najar')
	public function set_fields_db($array = array())
	{
		foreach($array as $field=>$value)
		{
			$this->db->setChild($field, $value);
		}

		if( !$this->db->asXml( PATH_PRIVATE.'plugins/'.$this->dir_name.'/db.xml' ) )
			return(false);

		return(true);
	}

	public function set_slug_name($name)
	{
		$name = strtolower($name);
		$name = str_replace(" ","_",$name);

		$this->slug_name = 'plugin_'.$name;
	}

	public function get_slug_name()
	{
		return( $this->slug_name );
	}

	public function set_attributes($args)
	{
		$this->name = $args['name'];
		$this->description = $args['description'];
		$this->author = $args['author'];
		$this->version = $args['version'];
		$this->url = $args['url'];

		if(isset($args['display']))
			$this->display = $args['display'];
	}

	public function get_name()
	{
		return( $this->name );
	}

	public function get_description()
	{
		return( $this->description );
	}

	public function get_author()
	{
		return( $this->author );
	}

	public function get_version()
	{
		return( $this->version );
	}

	public function get_url()
	{
		return( $this->url );
	}

	public function get_dir_name()
	{
		return( $this->dir_name );
	}

	public function display()
	{
		return( $this->display );
	}

	public function get_html_config()
	{
		return(false);
	}

	public function get_html()
	{
		return(false);
	}

}

?>