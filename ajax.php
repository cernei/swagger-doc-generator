<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function prefixEachLine($prefix, $string) {
    return $prefix . str_replace("\n", "\n" . $prefix, trim($string));

}
function makeAnnotationBlock($name, $text, $indentation = 0) {
    $string = '
@SWG\\' .$name. '(
' . prefixEachLine("\t", $text). '
)
';
    $string = prefixEachLine(' * ', $string);
    $string = '/**' . "\n".  $string . "\n" . ' */';
    if ($indentation) {
        $string = prefixEachLine("\t", $string);
    }
    return $string;
}

$yaml = <<<EOD
---
filter*:
 keyword: "test string" #test
 type: 0
 category: 1
sort*: last_modified_date.asc
pageNr*: 1
pageSize*: 50 
...
EOD;
$name = 'SearchGrants';
$template = '';
$required = [];
$parsed = yaml_parse($yaml);
$variables = [];

foreach ($parsed as $key => $item) {
    $keyWithoutAsterisk = $key;
    if (strpos($key, '*')!==false) {
        unset($parsed[$key]);
        $keyWithoutAsterisk = str_replace('*', '', $key);
        $parsed[$keyWithoutAsterisk] = $item;
        $required[] = $keyWithoutAsterisk;
    }
    if (is_numeric($item)) {
        $type = 'integer';
    } else if(is_string($item)) {
        $type = 'string';
    } else if(is_array($item)) {
        $type = 'array';
    }
    $varBlock = '
type="'.$type.'",
example=' . $item . '
';
    $variable = makeAnnotationBlock('Property', $varBlock) . "\n";
    $variable .= 'public $' . $keyWithoutAsterisk . ';';
    $variables[] = $variable;
}


$classTemplate = '
class ' . $name . '
{
'. prefixEachLine("\t", implode("\n\n", $variables)) .'
}
';
if ($required) {
    $requiredTemplate = '
required={
    "' . implode("\",\n\t\"", $required) . '"
},
type="object"
';
    $template .= makeAnnotationBlock('Definition', $requiredTemplate);
}

$template .= $classTemplate;

echo str_replace("\t", '    ', $template);
exit;
print_r($parsed);