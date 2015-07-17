<?php
class imdb 
{
	private $imdb_id;

	private $api_url = "http://api.douban.com/v2/movie";
	
	private $cache_dir = "./imdb/cache";
	
	private $cache_ttl = 86400;
	
	private $data;

	public function __construct($imdb_id)
	{
		$this->imdb_id = $imdb_id;
	}

	public function get_movie()
	{
		$cache_file = $this->cache_dir ."/". $this->imdb_id . ".json";
		if (!file_exists($cache_file) || time() - filemtime($cache_file) > $this->cache_ttl)
		{
			$url = "http://api.douban.com/v2/movie/imdb/tt" . $this->imdb_id;
			$this->data = json_decode(file_get_contents($url), TRUE);
			$fp = fopen($cache_file, "w");
			fwrite($fp, serialize($this->data));
			fclose($fp);
		}
		else
		{
			$this->data = unserialize(file_get_contents($cache_file));
		}
		return $this->data;
	}
	
	public function clear_cache()
	{
		$cache_file = $this->cache_dir ."/". $this->imdb_id . ".json";
		unlink($cache_file);
	}
	
	public function get_data($name)
	{
		switch ($name)
		{
			case 'country':
				return $this->data['attrs']['country'];
			case 'director':
				return $this->data['author'];
			case 'creator':
				return $this->data['attrs']['writer'];
			case 'writing':
				return $this->data['attrs']['writer'];
			case 'producer':
				return NULL;
			case 'cast':
				return $this->data['attrs']['cast'];
			case 'plot':
				return array();
			case 'plotoutline':
				return $this->data['summary'];
			case 'composer':
				return NULL;
			case 'genres':
				return $this->data['attrs']['movie_type'];
			case 'similiar_movies':
				return NULL;
			case 'title':
				return $this->data['title'];
			case 'transname':
				return $this->data['alt_title'];
			case 'alsoknow':
				return NULL;
			case 'runtime_all':
				return implode($this->data['attrs']['movie_duration'], ', ');
			case 'year':
				return implode($this->data['attrs']['year'], ', ');
			case 'votes':
				return $this->data['rating']['numRaters'];
			case 'rating':
				return $this->data['rating']['average'];
			case 'language':
				return implode($this->data['attrs']['language'], ', ');
			case 'tagline':
				$tags = array();
				foreach ($this->data['tags'] as $tag)
				{
					$tags[] = $tag['name'];
				}
				return implode($tags, ', ');
			case 'photo_localurl':
				return $this->data['image'];
			case 'alsoknow':
				return array();
		}
	}
}

?>
