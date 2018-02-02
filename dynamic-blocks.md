# Dynamic Blocks #

## Dynamic Blocks ##

Dynamic blocks do one or both of:
 * Dynamic routing (creating dynamic pages) for URLs below that of the page in which they are included.
 * Inject custom data into the block field data dynamically (via API calls, database access, calculations)
 
A dynamic block's definition.json MUST include the attribute "dynamic" with a value of 1 or true
with a php block class extending ```App\Models\Definitions\Block```.

This block class (and the file in which it is stored in the block's definitions directory) must be
named after the {definition-name}-v{definition-version} identifier for the block, where:
 * The first character will be uppercased.
 * Any instance of one or more non-alphanumeric characters are removed, and the following character
   uppercased.
 * All remaining characters will be identical to the block definition name.
 * A suffix will be appended in the form "V{version}".
 
For example, a dynamic block definition:
 
 ```/blocks/-my_ACE-custom--block/v1/definition.json``` would have a class
named 

```MyACECustomBlockV1``` and stored in 

```/blocks/-my_ACE-custom--block/v1/MyACECustomBlockV1.php```
   

### Dynamic Content ###

To modify the data returned in the 'fields' attribute for this block, the block class must implement the
Block::filterData() method.

### Dynamic Routing ###

Any block which wishes to support dynamic routing should implement the Block::route() method,
returning a new DynamicPage object if the requested route is matched and otherwise false.


