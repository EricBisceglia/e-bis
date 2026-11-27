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
 * Adds, updates, or deletes an entry in the JSON storage.
 *
 * @param   array   $entry    The entry to add.
 * @param   string  $id       The ID of the entry to update.
 * @param   string  $version  The version hash of the entry.
 * @param   bool    $delete   Whether to delete the entry.
 * @param   string  $move     The direction to move the entry ('up' or 'down').
 *
 * @return  void
 */

function entries_save( array   $entry           ,
                       string  $id      = ''    ,
                       string  $version = ''    ,
                       bool    $delete  = false ,
                       string  $move    = ''    ) : void
{
  // Stop here if the requested operation is invalid
  if(!in_array($move, array('', 'up', 'down'), true) || ($delete && $move !== ''))
    throw new InvalidArgumentException("Invalid entry operation.");

  // Stop here if trying to move or delete without an entry identifier
  if(($delete || $move !== '') && $id === '')
    throw new InvalidArgumentException("Missing entry identifier.");

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

    // Move an entry within its profile
    if($move !== '')
    {
      // Find the entry's current position
      $source_index = null;

      // Locate the index of the entry to move within the entries array
      foreach($entries as $index => $saved_entry)
      {
        if($saved_entry['id'] === $id)
        {
          $source_index = $index;
          break;
        }
      }

      // Stop here if the entry no longer exists in the current list
      if($source_index === null)
        throw new OutOfBoundsException("The entry no longer exists.");

      // Determine the profile of the entry to move
      $profile = $entries[$source_index]['profile'];

      // Stop here if the profile order has changed
      if(!hash_equals(entries_order_version($entries, $profile), $version))
        throw new DomainException("The profile order has changed.");

      // Collect the indexes of all entries belonging to the same profile
      $profile_indexes = array();

      // Iterate through entries to find those that belong to the same profile
      foreach($entries as $index => $saved_entry)
      {
        if($saved_entry['profile'] === $profile)
          $profile_indexes[] = $index;
      }

      // Determine the current position of the entry within its profile's list
      $position = array_search($source_index, $profile_indexes, true);
      $target_position = $position + (($move === 'up') ? 1 : -1);

      // Stop here if the target position is out of bounds
      if(!isset($profile_indexes[$target_position]))
        return;

      // Swap the entry with the target entry within the profile's list
      $target_index = $profile_indexes[$target_position];
      $temporary_entry        = $entries[$source_index];
      $entries[$source_index] = $entries[$target_index];
      $entries[$target_index] = $temporary_entry;
    }

    // Add a new entry
    else if($id === '')
    {
      $entry['id']      = bin2hex(random_bytes(16));
      $entry['created'] = gmdate('c');
      $entries[]        = $entry;
    }

    // Update an existing entry
    else
    {
      // Look for the entry
      $entry_found = false;
      foreach($entries as $index => $saved_entry)
      {
        // Skip entries that don't match the ID
        if($saved_entry['id'] !== $id)
          continue;

        // Stop here if the entry has been modified since it was last fetched
        $saved_version = hash('sha256', json_encode($saved_entry, JSON_THROW_ON_ERROR));
        if(!hash_equals($saved_version, $version))
          throw new DomainException("The entry has changed.");

        // Delete the entry if requested
        if($delete)
        {
          array_splice($entries, $index, 1);
          $entry_found = true;
          break;
        }

        // Preserve the entry's identity and creation date
        $entry['id']      = $saved_entry['id'];
        $entry['created'] = $saved_entry['created'];
        $entry['updated'] = gmdate('c');

        // Replace the saved entry with the updated one
        $entries[$index] = array_replace($saved_entry, $entry);
        $entry_found    = true;
        break;
      }

      // Stop here if the entry wasn't found
      if(!$entry_found)
        throw new OutOfBoundsException("The entry no longer exists.");
    }

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




/**
 * Returns a version hash of a profile's entry order.
 *
 * @param   array   $entries  The saved entries.
 * @param   string  $profile  The profile to check.
 *
 * @return  string            The order's version hash.
 */

function entries_order_version( array   $entries ,
                                string  $profile ) : string
{
  // Fetch the identifiers of the entries for the given profile
  $identifiers = array();
  foreach($entries as $entry)
  {
    if($entry['profile'] === $profile)
      $identifiers[] = $entry['id'];
  }

  // Return a hash of the identifiers to represent the order version
  return hash('sha256', json_encode($identifiers, JSON_THROW_ON_ERROR));
}