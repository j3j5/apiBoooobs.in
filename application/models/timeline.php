<?php


class Timeline extends Eloquent {

	public static $table  = 'timelines';

	/**
	 * Encode the array with the timeline information.
	 *
	 * @param Array The timeline info.
	 *
	 * @return String|Bool The compressed string to be stored as a blob.
	 *
	 * @author Julio Foulquié <julio@tnwlabs.com>
	 */
	public static function build_blob($timeline) {
		if(empty($timeline)) {
			$timeline = array();
		}
		return gzdeflate(serialize($timeline));
	}

	/**
	 * Decodes a timeline that has been retrieve from the db.
	 *
	 * @param String $blob The timeline as stored on the DB.
	 *
	 * @return Array|Bool The timeline in its array form or FALSE
	 *
	 * @author Julio Foulquié <julio@tnwlabs.com>
	 */
	public static function decode_blob($blob) {
		if(empty($blob)) {
			return FALSE;
		}
		return unserialize(gzinflate($blob));
	}

}
