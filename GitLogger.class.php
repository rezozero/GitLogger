<?php 
/**
 *
 * @file GitLogger.class.php
 * @copyright REZO ZERO 2013
 * @author Ambroise Maupate
 */
class GitLogger
{
	private $repositoryPath = null;
	private $binaryPath = null;
	private $logCount = 50;
	private $since = null;
	private $commits = null;

	private static $data = array(
		'description'=>'%b'
	);
	
	/**
	 * 
	 * @param string $repositoryPath
	 * @param string $binaryPath    
	 */
	function __construct( $repositoryPath, $binaryPath = "/usr/bin/git" )
	{
		$this->repositoryPath = $repositoryPath;
		$this->binaryPath = $binaryPath;
		$this->since = time();
	}

	private function getSingleCommand( $hash, $format )
	{
		return $this->binaryPath.
					" log --pretty=format:'".
					$format.
					"' -n1 ".$hash;
	}

	private function getTagCommand( $hash )
	{
		return $this->binaryPath.
					" describe --tags ".
					$hash;
	}

	public function setCount( $count )
	{
		$this->logCount = (int)$count;
	}
	public function setSince( $date )
	{
		$this->since = strtotime($date);
	}

	public function getRevList()
	{
		$output = "";
		chdir( $this->repositoryPath );
		exec($this->binaryPath.
			' rev-list HEAD --max-count='.$this->logCount.
			' --until='.date("Y-m-d",$this->since).
			' --abbrev-commit', $output);

		return $output;
	}

	public function getJSON()
	{
		return json_encode($this->getArray());
	}

	public function &getArray()
	{
		chdir( $this->repositoryPath );

		$hashes = $this->getRevList();
		$this->commits = array();

		for ($i=0; $i < count($hashes); $i++) { 
			$commit = array();

			$output = "";
			exec( $this->getSingleCommand($hashes[$i], "%s\n%cn\n%at") , $output );
			$commit['title'] = trim(addslashes($output[0]));
			$commit['author'] = trim(addslashes($output[1]));
			$commit['date'] = trim($output[2]);
			$commit['hash'] = trim($hashes[$i]);

			//	Tags
			$tagOutput = '';
			exec( $this->getTagCommand($hashes[$i]), $tagOutput );
			$commit['tag'] = trim(addslashes($tagOutput[0]));

			if (empty($commit['tag']) || strpos($commit['tag'], 'g'.$hashes[$i]) !== false) {
				unset($commit['tag']);
			}


			foreach (static::$data as $key => $value) {
				$output = "";
				exec( $this->getSingleCommand($hashes[$i], $value) , $output );
				$commit[$key] = trim(addslashes(implode("\n", $output)));
			}
			
			$this->commits[] = $commit;
			unset($output);
			unset($commit);
		}


		return $this->commits;
	}
}

