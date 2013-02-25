<?php

/*
 * Nibbleblog -
 * http://www.nibbleblog.com
 * Author Diego Najar

 * All Nibbleblog code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
*/

class Session {

	public static function init()
	{
		unset($_SESSION['nibbleblog']);

		$comment = array('author_name'=>'', 'author_email'=>'', 'content'=>'', 'post_allow_comments'=>false, 'id_post'=>0);

		$_SESSION['nibbleblog'] = array(
							'error'=>false,
							'alert'=>'',
							'captcha'=>Text::random_text(64),
							'comment'=>$comment
		);
	}

	public static function get_error()
	{
		return($_SESSION['nibbleblog']['error']);
	}

	public static function set_error($boolean = true)
	{
		$_SESSION['nibbleblog']['error'] = $boolean;
	}

	public static function get_alert()
	{
		self::set_error(false);
		return($_SESSION['nibbleblog']['alert']);
	}

	public static function set_alert($text = '')
	{
		$_SESSION['nibbleblog']['alert'] = $text;
	}

	public static function get_captcha()
	{
		return($_SESSION['nibbleblog']['captcha']);
	}

	public static function set_comment($comment)
	{
		$_SESSION['nibbleblog']['comment']['author_name'] = $comment['author_name'];
		$_SESSION['nibbleblog']['comment']['author_email'] = $comment['author_email'];
		$_SESSION['nibbleblog']['comment']['content'] = $comment['content'];
		$_SESSION['nibbleblog']['comment']['post_allow_comments'] = $comment['post_allow_comments'];
		$_SESSION['nibbleblog']['comment']['id_post'] = $comment['id_post'];
	}

	public static function get_comment($key)
	{
		if(isset($_SESSION['nibbleblog']['comment'][$key]))
		{
			return($_SESSION['nibbleblog']['comment'][$key]);
		}

		return false;
	}

	public static function get_comment_array()
	{
		return($_SESSION['nibbleblog']['comment']);
	}

}

?>