<?php if(realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) { http_response_code(403); exit; } /***************/
/*                                                                                                                   */
/*                                                   ENTRY STORAGE                                                   */
/*                                                                                                                   */
/*********************************************************************************************************************/

 /**
 * Fetches the saved entries.
 *
 * @return  array  The saved entries.
 */

function entries_get() : array
{
  // Read the JSON file containing the entries
  $content = @file_get_contents(__DIR__.'/../data/entries.json');

  // Stop here if the file couldn't be read
  if($content === false)
    throw new RuntimeException("Could not read entries.json.");

  // Decode and validate the entry list
  $entries = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

  // Stop here if the entry list is unusable
  if(!is_array($entries) || !array_is_list($entries))
    throw new RuntimeException("Invalid entry list.");

  // Return the entry list
  return $entries;
}




 /**
 * Adds an entry to the JSON storage.
 *
 * @param   array  $entry  The entry to add.
 *
 * @return  void
 */

function entries_add( array $entry ) : void
{
  // Open the lock file (to make sure no other process is editing the entries)
  $lock = @fopen(__DIR__.'/../data/entries.lock', 'c');

  // Stop here if the lock file can't be opened
  if($lock === false)
    throw new RuntimeException("Could not open the entry lock.");

  // Track a future temporary file for cleanup
  $temporary_file = false;

  // Lock the operations and perform the addition
  try
  {
    // Lock the file for exclusive access
    if(!flock($lock, LOCK_EX))
      throw new RuntimeException("Could not lock entry storage.");

    // Fetch the latest entries
    $entries = entries_get();

    // Give the new entry a unique ID and timestamp
    $entry['id']      = bin2hex(random_bytes(16));
    $entry['created'] = gmdate('c');

    // Add the new entry to the entry list
    $entries[] = $entry;

    // Encode the complete updated list
    $content = json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";

    // Write a temporary file with the updated entries
    $temporary_file = @tempnam(__DIR__.'/../data', 'entries-');

    // Stop here if the temporary file couldn't be created
    if($temporary_file === false)
      throw new RuntimeException("Could not create temporary entry storage.");

    // Write the updated content to the temporary file
    if(@file_put_contents($temporary_file, $content) !== strlen($content))
      throw new RuntimeException("Could not write entry storage.");

    // Replace the original file with the temporary file
    if(!@rename($temporary_file, __DIR__.'/../data/entries.json'))
      throw new RuntimeException("Could not replace entry storage.");

    // Stop tracking the temporary file
    $temporary_file = false;
  }
  finally
  {
    // Clean up any remaining temp file
    if($temporary_file !== false && is_file($temporary_file))
      @unlink($temporary_file);

    // Unlock the lock!
    flock($lock, LOCK_UN);
    fclose($lock);
  }
}




 /**
 * Reads a submitted text field.
 *
 * @param   mixed  $value The submitted value.
 *
 * @return  string        The text.
 */

function entry_input( mixed $value ) : string
{
  // Make sure the value is a string
  if(!is_string($value))
    throw new InvalidArgumentException("Invalid entry field.");

  // Format the text and return it
  return trim(str_replace(array("\r\n", "\r"), "\n", $value));
}