<?php /*

Notes:
CommitID increments by 1, regardless of what branch it's in.
When switching between branches, access all commits between the designated branch versions.

---------------------------------------------
------ About the VersionControl Class ------
---------------------------------------------

This plugin provides a tool for version control in databases. No updates are required to any schema; the instruction sets will handle all changes between branches. It can also be used to identify conflicts and merge.


--------------------------------------------
------ Generating the instruction set ------
--------------------------------------------

All instructions are added with a single method: addInstruction(). These are then tracked to move back and forth between versions.

	$VCHandler->addInstruction($table, $rowID, "columnToUpdate", "Previous Value", "New Value");


-------------------------------------------
------ Example of serialized result  ------
-------------------------------------------

	{
		"exampleTable" :
		{
			"~uniqueKey" : "id"
		
		,	"50":
			[
				["columnToUpdate", "previousValue", "newValue"]
			,	["columnToUpdate2", "prevValue", "newerVal"]
			,	["anotherColumn", "original", "later"]
			]
		
		,	"18":
			[
				["someColumn", "original value to change", "new branch goes to this"]
			]
		}
	}
	
	
// The SQL generated from the example when moving to a new branch
UPDATE `exampleTable` SET `columnToUpdate` = 'newValue', `columnToUpdate2` = 'newerVal', `anotherColumn` = 'later' WHERE `id` = '50' LIMIT 1
UPDATE `exampleTable` SET `someColumn` = 'new branch goes to this' WHERE `id` = '18' LIMIT 1 


// The SQL generated from the example when moving to the previous branch
UPDATE `exampleTable` SET `columnToUpdate` = 'previousValue', `columnToUpdate2` = 'prevValue', `anotherColumn` = 'original' WHERE `id` = '50' LIMIT 1
UPDATE `exampleTable` SET `someColumn` = 'original value to change' WHERE `id` = '18' LIMIT 1 


---------------------------------------
------ Tables for VersionControl ------
---------------------------------------
	
	Database::exec("
	CREATE TABLE IF NOT EXISTS `version_control_commits`
	(
		`branch_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
		`commit_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
		
		`author`				varchar(64)					NOT NULL	DEFAULT '',
		`description`			varchar(200)				NOT NULL	DEFAULT '',
		`timestamp`				int(10)			unsigned	NOT NULL	DEFAULT '0',
		
		`instruction_set`		longtext					NOT NULL	DEFAULT '',
		
		UNIQUE (`branch_id`, `commit_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(branch_id, commit_id) PARTITIONS 31;
	");
	
	
-------------------------------
------ Methods Available ------
-------------------------------

// Adds an instruction to the version
$VCHandler->addInstruction($tableName, $rowID, $column, $previousValue, $finalValue)

// Convert instruction sets to SQL - can process forward or backward version movement
$VCHandler->convertToBranchAhead()
$VCHandler->convertToBranchBehind()

// Makes a new commit using the instructions processed
$VCHandler->commit($branchID, $description, $author)

// Updates to a new version
$VCHandler->updateToVersion($branchIDTo, $branchIDFrom)

// Handle the active commit ID
$commitID = $VCHandler->getNextCommitID()
$commitID = $VCHandler->UpdateCommitID()

*/

class VersionControl {
	
	
/****** Class Variables ******/
	public $instructionSet = array();		// <str:mixed> Stores an instruction set to add to the database.
	public $commitSequence = array();		// <str:str> A list of SQL commands to commit.
	
	
/****** Construct this version control handler ******/
	public function __construct (
	)
	
	// $VCHandler = new VersionControl()
	{
		// TODO: prep any necessary database entries that are unavailable
	}
	
	
/****** Add a new instruction ******/
	public function addInstruction
	(
		$tableName			// <str> The name of the table being added to the instruction.
	,	$rowID				// <T> The ID of the row that's being updated.
	,	$column				// <str> The column that's being updated.
	,	$previousValue		// <str> The previous value of the instruction.
	,	$finalValue			// <str> The final value to assign.
	)						// RETURNS <void>
	
	// $VCHandler->addInstruction($tableName, $rowID, $column, $previousValue, $finalValue)
	{
		// If the table doesn't exist yet
		if(empty($this->instructionSet[$tableName]))
		{
			$this->instructionSet[$tableName]['~uniqueKey'] = 'id';
		}
		
		// Assign the row
		$this->instructionSet[$tableName][$rowID][] = [$column, $previousValue, $finalValue];
	}
	
	
/****** Convert instructions to new branch ******/
	public function convertToBranchAhead (
	)				// RETURNS <void>
	
	// $VCHandler->convertToBranchAhead()
	{
		return $this->convertToSQL(true);
	}
	
	
/****** Convert instructions to previous branch ******/
	public function convertToBranchBehind (
	)				// RETURNS <void>
	
	// $VCHandler->convertToBranchBehind()
	{
		return $this->convertToSQL(false);
	}
	
	
/****** Convert instructions to SQL ******/
	private function convertToSQL
	(
		$moveForward = true		// <bool> TRUE if we're moving to the newer branch, FALSE for previous branch.
	)							// RETURNS <void>
	
	// $VCHandler->convertToSQL($moveForward)
	{
		// Set a value that indicates which column will be accessed - based on which branch we're moving toward:
		$columnChoose = ($moveForward ? 2 : 1);
		
		// Loop through each table
		foreach($this->instructionSet as $table => $tableData)
		{
			// Extract the unique key
			$uniqueKey = $tableData['~uniqueKey'];
			
			// Remove the unique key from the list (for looping)
			unset($tableData['~uniqueKey']);
			
			// Loop through each row
			foreach($tableData as $rowID => $updateList)
			{
				// Prepare Row Values
				$newCommand = "";
				$rowLoop = 0;
				
				// Loop through each row's updates
				foreach($updateList as $nextUpdate)
				{
					// On the first row pass, create the update; otherwise, separate the next command with a comma
					$newCommand .= ($rowLoop == 0 ? "UPDATE `" . $table . "` SET " : ", ");
					
					// Add the next column update
					// If we're moving to new branches, it will choose column 2; otherwise, it will choose column 1
					$newCommand .=  "`" . $nextUpdate[0] . "` = '" . addslashes($nextUpdate[$columnChoose]) . "'";
					
					$rowLoop++;
				}
				
				$newCommand .= " WHERE `" . $uniqueKey . "` = '" . addslashes($rowID) . "' LIMIT 1";
				
				echo "<br />" . $newCommand;
			}
		}
	}
	
	
/****** Commit the instructions ******/
	public function commit
	(
		$branchID			// <int> The ID of the branch to commit to.
	,	$description		// <str> The description of the commit.
	,	$author = ""		// <str> The author responsible for this commit.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $VCHandler->commit($branchID, $description, $author)
	{
		// Get the next commit ID available
		$commitID = $this->getNextCommitID();
		
		// We need to make several changes at once - transaction required
		Database::startTransaction();
		
		// Commit this instruction sequence to the branch
		if(!Database::query("INSERT INTO `version_control_instructions` (branch_id, commit_id, instruction_set) VALUES (?, ?, ?)", array($branchID, $commitID, json_encode($this->instructionSet))))
		{
			return Database::endTransaction(false);
		}
		
		// Update to the next commit ID
		return Database::endTransaction($this->updateCommitID());
	}
	
	
/****** Update to a version from your current version ******/
	public function updateToVersion
	(
		$versionIDTo		// <int> The ID of the version to update to.
	,	$versionIDFrom		// <int> The ID of the version you're currently on.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $VCHandler->updateToBranch($branchIDTo, $branchIDFrom)
	{
		// If the versions are identical, we don't need to run this function
		if($versionIDTo == $versionIDFrom) { return false; }
		
		// Prepare Values
		$orderDirection = ($versionIDTo > $versionIDFrom ? "ASC" : "DESC");
		
		// TODO: Allow branchID to be something other than master
		$branchID = 0;
		
		// TODO: We need to run integrity checks on these versions
		if(!$this->verifyVersion($branchID, $versionIDTo)) { return false; }
		if(!$this->verifyVersion($branchID, $versionIDFrom)) { return false; }
		
		// Pull all instructions sets from current to previous, in appropriate order
		if($results = Database::selectMultiple("SELECT instruction_set FROM version_control_commits WHERE branch_id=? AND commit_id BETWEEN ? AND ? ORDER BY commit_id " . $orderDirection, array($branchID, $versionIDTo, $versionIDFrom)))
		{
			$success = true;
			
			// Begin database transaction to preserve integrity
			Database::startTransaction();
			
			// Loop through each instruction set and process it
			foreach($result as $ins)
			{
				// Process the instruction
				// If the instruction fails - end the transaction
				if(!$success = $this->processInstructions($ins))
				{
					break;
				}
			}
			
			// If everything was successful, finish the transaction
			return Database::endTransaction($success);
		}
		
		// Nothing processed
		return false;
	}
	
	
/****** Verify the version exists ******/
	public function verifyVersion
	(
		$branchID		// <int> The branch ID that you're verifying with.
	,	$versionID		// <int> The version ID that you're verifying.
	,	$strict = true	// <bool> TRUE if the version must be a strict merge.
	)					// RETURNS <bool> TRUE if the version is verified.
	
	// $VCHandler->verifyVersion($branchID, $versionID, $strict = true)
	{
		/*
			Conditions for the version to be verified:
				1. Branch must exist.
				2. Version must be within commit range.
				3. For strict verification, the commit chosen must have been specific to this branch.
		*/
		
		// TODO: Run verification
		return true;
	}
	
	
/****** Get the next commit ID ******/
	public function getNextCommitID (
	)				// RETURNS <int> The next CommitID
	
	// $commitID = $VCHandler->getNextCommitID()
	{
		// TODO: return the next commit ID in the sequence
		return 1;
	}
	
	
/****** Get the next commit ID ******/
	public function updateCommitID (
	)				// RETURNS <bool> TRUE if the CommitID was updated, FALSE if not.
	
	// $commitID = $VCHandler->getNextCommitID()
	{
		// TODO: set the next commit ID (increment by 1)
		return true;
	}
	
	
/****** Show the difference between two versions ******/
	public function getDiff
	(
		$branchID1			// <int> The ID of the branch to begin the diff on.
	,	$branchID2			// <int> The ID of the branch to end the diff on.
	,	$versionID1			// <int> The ID of the version in branch 1 to start the diff on.
	,	$versionID2			// <int> The ID of the version in branch 2 to end the diff on.
	)						// RETURNS <str> A dump of the diff.
	
	// $diffDump = $VCHandler->getDiff($branchID1, $branchID2, $versionID1, $versionID2)
	{
		// Set the lower version as #1 if the user didn't. This keeps consistency.
		if($versionID2 < $versionID1)
		{
			list($branchID1, $branchID2, $versionID1, $versionID2) = array($branchID2, $branchID1, $versionID2, $versionID1);
		}
		
		// If the branches are identical, we can do a simple diff
		if($branchID1 == $branchID2)
		{
			$diff = $this->getSimpleDiff($branchID1, $versionID1, $versionID2);
		}
		
		// Otherwise, run a diff through multiple branches
		else
		{
			$diff = $this->getDiffAcrossBranches($branchID1, $branchID2, $versionID1, $versionID2);
		}
		
		// TODO: Return diff in human-readable form
		return $this->getDiffDump($diff);
	}
	
	
/****** Show the difference between two versions in a single branch ******/
	private function getSimpleDiff
	(
		$branchID			// <int> The ID of the branch to gather a diff in.
	,	$versionPast		// <int> The ID of the past version.
	,	$versionFuture		// <int> The ID of the future version.
	)						// RETURNS <str> A dump of the diff.
	
	// $diffDump = $VCHandler->getSimpleDiff($branchID, $versionPast, $versionFuture)
	{
		// Prepare the diff
		$diff = array();
		
		// Pull all commits between $versionPast and $versionFuture
		$results = Database::selectMultiple("SELECT instruction_set FROM version_control_commits WHERE branch_id=? AND commit_id BETWEEN ? AND ? ORDER BY commit_id=?", array($branchID, $versionPast, $versionFuture));
		
		// Loop through each commit
		foreach($results as $instruction)
		{
			// Loop through each table in the instruction set
			foreach($instruction['instruction_set'] as $table => $tableData)
			{
				// Extract the unique key
				$uniqueKey = $tableData['~uniqueKey'];
				
				// Remove the unique key from the list (for looping)
				unset($tableData['~uniqueKey']);
				
				// Loop through each row
				foreach($tableData as $rowID => $updateList)
				{
					// Loop through each row's updates
					foreach($updateList as $nextUpdate)
					{
						// Check if the diff entry for this row/column has already been created.
						if(isset($diff[$table][$rowID][$nextUpdate[0]]))
						{
							// Don't overwrite the past version for thiw row/column
							$diff[$table][$rowID][$nextUpdate[0]]["future"] = $nextUpdate[2];
						}
						
						// If the diff entry hasn't been created, copy the exact contents.
						else
						{
							$diff[$table][$rowID][$nextUpdate[0]] = array("past" => $nextUpdate[1], "future" => $nextUpdate[2]);
						}
					}
				}
			}
		}
		
		// Return the diff
		return $diff;
	}
	
	
/****** Show the difference between two versions across two branches ******/
	private function getDiffAcrossBranches
	(
		$branchPast			// <int> The ID of the past branch.
	,	$branchFuture		// <int> The ID of the future branch.
	,	$versionPast		// <int> The ID of the previous version. 
	,	$versionFuture		// <int> The ID of the version in branch 2 to end the diff on.
	)						// RETURNS <str> A dump of the diff.
	
	// $diffDump = $VCHandler->getDiffAcrossBranches($branchPast, $branchFuture, $versionPast, $versionFuture)
	{
		// TODO: identify branching mechanisms
		// TODO: return a diff across branches
		return '';
	}
	
}
