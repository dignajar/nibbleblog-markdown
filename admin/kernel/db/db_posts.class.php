<?php

/*
 * Nibbleblog -
 * http://www.nibbleblog.com
 * Author Diego Najar

 * Last update: 15/07/2012

 * All Nibbleblog code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
*/

class DB_POSTS {

/*
======================================================================================
	VARIABLES
======================================================================================
*/
		public $file_xml; 			// Contains the link to XML file
		public $obj_xml; 				// Contains the object

		private $files;
		private $files_count;

		private $last_insert_id;

		private $settings;

/*
======================================================================================
	CONSTRUCTORS
======================================================================================
*/
		function DB_POSTS($file, $settings)
		{
			$this->file_xml = $file;

			if(file_exists($this->file_xml))
			{
				$this->settings = $settings;

				$this->last_insert_id = max($this->get_autoinc() - 1, 0);

				$this->files = array();
				$this->files_count = 0;

				$this->obj_xml = new NBXML($this->file_xml, 0, TRUE, '', FALSE);
			}
			else
			{
				return(false);
			}

			return(true);
		}

/*
======================================================================================
	PUBLIC METHODS
======================================================================================
*/
		public function savetofile()
		{
			return( $this->obj_xml->asXML($this->file_xml) );
		}

		public function get_last_insert_id()
		{
			return( $this->last_insert_id );
		}

		// Return the POST ID
		public function add($args)
		{
			global $_DATE;

			// Template
			$xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
			$xml .= '<post>';
			$xml .= '</post>';

			// Object
			$new_obj = new NBXML($xml, 0, FALSE, '', FALSE);

			// Time - UTC=0
			$time_unix = $_DATE->unixstamp();

			// Time for Filename
			$time_filename = $_DATE->format_gmt($time_unix, 'Y.m.d.H.i.s');

			// Elements
			$new_obj->addChild('type',				$args['type']);
			$new_obj->addChild('title',				$args['title']);
			$new_obj->addChild('content',			$args['content']);
			$new_obj->addChild('description',		$args['description']);
			$new_obj->addChild('allow_comments',	$args['allow_comments']);

			$new_obj->addChild('pub_date',			$time_unix);
			$new_obj->addChild('mod_date',			'0');
			$new_obj->addChild('visits',			'0');

			// Video or Quote post
			if(isset($args['video']))
			{
				$new_obj->addChild('video', $args['video']);
			}
			elseif(isset($args['quote']))
			{
				$new_obj->addChild('quote', $args['quote']);
			}

			// Last insert ID
			$new_id = $this->last_insert_id = $this->get_autoinc();

			// Filename for new post
			$filename = $new_id . '.' . $args['id_cat'] . '.' . $args['id_user'] . '.NULL.' . $time_filename . '.xml';

			// Save to file
			if( $new_obj->asXml( PATH_POSTS . $filename ) )
			{
				// Is Sticky post ?
				if( $args['sticky'] == 1 )
					$this->add_sticky( $new_id );

				// Increment the AutoINC
				$this->set_autoinc(1);

				// Save config file post.xml
				$this->savetofile();
			}
			else
			{
				return(false);
			}

			return($new_id);
		}

		public function set($args)
		{
			global $_DATE;

			$this->set_file( $args['id'] );

			// Post not found
			if($this->files_count == 0)
			{
				return(false);
			}

			$new_obj = new NBXML(PATH_POSTS . $this->files[0], 0, TRUE, '', FALSE);

			$new_obj->setChild('title', 			$args['title']);
			$new_obj->setChild('content', 			$args['content']);
			$new_obj->setChild('description', 		$args['description']);
			$new_obj->setChild('mod_date', 			$_DATE->unixstamp());
			$new_obj->setChild('allow_comments', 	$args['allow_comments']);

			if( $args['sticky'] == 1 )
			{
				$this->add_sticky( $args['id'] );
			}
			else
			{
				$this->remove_sticky( $args['id'] );
			}

			if(isset($args['quote']))
			{
				$new_obj->setChild('quote', $args['quote']);
			}

			// Save config file post.xml
			$this->savetofile();

			// Save to file the post
			return($new_obj->asXml( PATH_POSTS . $this->files[0] ) );
		}

		public function change_category($args)
		{
			$this->set_file( $args['id'] );

			// Post not found
			if($this->files_count == 0)
			{
				return(false);
			}

			$filename = $this->files[0];

			$explode = explode('.', $filename);
			$explode[1] = $args['id_cat'];
			$implode = implode('.', $explode);

			return( rename( PATH_POSTS.$filename, PATH_POSTS.$implode ) );
		}

		public function remove($args)
		{
			$this->set_file( $args['id'] );

			if($this->files_count > 0)
			{
				return(unlink( PATH_POSTS . $this->files[0] ));
			}
			else
			{
				return(false);
			}

			return(true);
		}

		public function get($args)
		{
			$this->set_file($args['id']);

			if($this->files_count > 0)
				return( $this->get_items( $this->files[0] ) );
			else
				return( array() );
		}

		public function get_list_by_page($args)
		{
			// Set the list of post
			$this->set_files();

			if($this->files_count > 0)
				return( $this->get_list_by($args['page'], $args['amount']) );
			else
				return( array() );
		}

		public function get_list_by_category($args)
		{
			$this->set_files_by_category($args['id_cat']);

			if($this->files_count > 0)
				return( $this->get_list_by($args['page'], $args['amount']) );
			else
				return( array() );
		}

		public function get_list_by_sticky()
		{
			$tmp_array = array();
			foreach( $this->obj_xml->sticky->id as $id )
			{
				$this->set_file((int)$id);
				array_push( $tmp_array, $this->get_items( $this->files[0] ) );
			}

			return( $tmp_array );
		}


		public function get_list_by_tag($dbxml_tags, $page_number, $post_per_page)
		{
			return( array() );
		}

		public function get_list_by_archives($month, $year, $page_number, $post_per_page)
		{
			return( array() );
		}

		public function get_count()
		{
			return( $this->files_count );
		}

		public function get_autoinc()
		{
			return( (int) $this->obj_xml['autoinc'] );
		}

/*
======================================================================================
	PRIVATE METHODS
======================================================================================
*/
		private function set_autoinc($value = 0)
		{
			$this->obj_xml['autoinc'] = $value + $this->get_autoinc();
		}

		public function add_sticky($id)
		{
			if( !$this->is_sticky($id)  )
				$this->obj_xml->sticky->addChild('id', $id);
		}

		public function remove_sticky($id)
		{
			if( $this->is_sticky($id)  )
			{
				$tmp_node = $this->obj_xml->xpath('/post/sticky/id[.="'.$id.'"]');
				$dom = dom_import_simplexml($tmp_node[0]);
				$dom->parentNode->removeChild($dom);
			}
		}

		private function set_file($id)
		{
			global $_FS;

			$this->files = $_FS->ls(PATH_POSTS, $id.'.*.*.*.*.*.*.*.*.*', 'xml', false, false, false);
			$this->files_count = count( $this->files );
		}

		// setea los parametros de la clase
		// obtiene todos los archivos post
		private function set_files()
		{
			global $_FS;

			$this->files = $_FS->ls(PATH_POSTS, '*', 'xml', false, false, true);
			$this->files_count = count( $this->files );
		}

		private function set_files_by_category($id_cat)
		{
			global $_FS;

			$this->files = $_FS->ls(PATH_POSTS, '*.'.$id_cat.'.*.*.*.*.*.*.*.*', 'xml', false, false, true);
			$this->files_count = count( $this->files );
		}

		// Devuelve los items de un post
		// File name: ID_POST.ID_CATEGORY.ID_USER.NULL.YYYY.MM.DD.HH.mm.ss.xml
		private function get_items($file)
		{
			global $_TEXT;
			global $_DATE;

			$obj_xml = new NBXML(PATH_POSTS . $file, 0, TRUE, '', FALSE);

			$file_info = explode('.', $file);

			$content = (string) $obj_xml->getChild('content');
			$tmp_content = explode("<!-- pagebreak -->", $content);

			$tmp_array = array('read_more'=>false);

			$tmp_array['filename']			= (string) $file;

			$tmp_array['id']				= (int) $file_info[0];
			$tmp_array['id_cat']			= (int) $file_info[1];
			$tmp_array['id_user']			= (int) $file_info[2];
			$tmp_array['visits']			= (int) $obj_xml->getChild('visits');

			$tmp_array['type']				= (string) $obj_xml->getChild('type');
			$tmp_array['title']				= (string) $obj_xml->getChild('title');
			$tmp_array['description']		= (string) $obj_xml->getChild('description');

			$tmp_array['pub_date_unix']		= (string) $obj_xml->getChild('pub_date');
			$tmp_array['mod_date_unix']		= (string) $obj_xml->getChild('mod_date');

			$tmp_array['allow_comments']	= (bool) ((int)$obj_xml->getChild('allow_comments'))==1;
			$tmp_array['sticky']			= (bool) $this->is_sticky($file_info[0]);

			// DATE
			$tmp_array['pub_date'] = $_DATE->format($tmp_array['pub_date_unix'], $this->settings['timestamp_format']);
			$tmp_array['mod_date'] = $_DATE->format($tmp_array['mod_date_unix'], $this->settings['timestamp_format']);

			// CONTENT
			$tmp_array['content'][0] = $content;

			$tmp_array['content'][1] = $tmp_content[0];

			if( isset($tmp_content[1]) )
			{
				$tmp_array['content'][2] = $tmp_content[1];
				$tmp_array['read_more'] = true;
			}

			// POST TYPE
			if($tmp_array['type']=='video')
			{
				$tmp_array['video']			= (string) $obj_xml->getChild('video');
			}
			elseif($tmp_array['type']=='quote')
			{
				$tmp_array['quote']			= (string) $obj_xml->getChild('quote');
			}

			// FRIENDLY URLS
			if( $this->settings['friendly_urls'] )
			{
				if( $_TEXT->not_empty($tmp_array['title']))
				{
					$slug = $_TEXT->clean_url($tmp_array['title']);
				}
				else
				{
					$slug = $tmp_array['type'];
				}

				$tmp_array['permalink'] = HTML_PATH_ROOT.'post-'.$tmp_array['id'].'/'.$slug;
			}
			else
			{
				$tmp_array['permalink'] = HTML_PATH_ROOT.'index.php?controller=post&action=view&id_post='.$tmp_array['id'];
			}

			return( $tmp_array );
		}

		private function is_sticky($id)
		{
			return( $this->obj_xml->xpath('/post/sticky/id[.="'.$id.'"]') != array() );
		}

		private function get_list_by($page_number, $post_per_page)
		{
			$init = (int) $post_per_page * $page_number;
			$end  = (int) min( ($init + $post_per_page - 1), $this->files_count - 1 );
			$outrange = $init > $end;

			$tmp_array = array();

			if( !$outrange )
			{
				for($init; $init <= $end; $init++)
				{
					array_push( $tmp_array, $this->get_items( $this->files[$init] ) );
				}
			}

			return( $tmp_array );
		}

} // END Class

?>
