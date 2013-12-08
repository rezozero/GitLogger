## What is it ?

GitLogger is a simple PHP tool to extract a git log from your repository into a PHP array or JSON string.

## Example

	$logger = new GitLogger("/home/myUser/myRepository.git", "/usr/bin/git");
	$logger->setCount(10);
	$logs = $logger->getArray();

	foreach ($logs as $key => $commit) {
		?><article>
			<h2><?php echo stripslashes($commit['title']) ?></h2>
			<author class="author">by <?php echo $commit['author'] ?></author>
			<date><?php echo strftime("%A %d %b. %Y - %R",$commit['date']); ?></date>
			<div class="description"><?php echo stripslashes($commit['description']); ?></div>
		</article><?php
	}