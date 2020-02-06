$d = dir(DRUPAL_ROOT."/sites/default/files/snowpit-profiles");
echo "Handle: " . $d->handle . "\n";
echo "Path: " . $d->path . "\n";
$result = '';
while ((false !== ($entry = $d->read())) && $n<5000 ) {
   $n +=1;
	 if ( preg_match ( '/^(graph|layers)-(\d{1,3})(\d\d\d).(png|jpg)$/' , $entry, $matches ) ){
	   $newpath = DRUPAL_ROOT."/sites/default/files/snowpit-profiles/$matches[2]/$matches[1]/$matches[1]-$matches[2]$matches[3].$matches[4]";
	 
     $result .= $d->path."/".$entry.": ". $newpath."\n";
		 if(rename ( $d->path."/".$entry , $newpath)){
		   $result .= "success: ". $d->path."/".$entry.": ". $newpath."\n";
		 
		 }else{
		   $result .= "NOT success: ". $d->path."/".$entry;
		 }
	 }else {
	   $result .= "No action: ".$entry."\n"; 
	 }
	 
}
$d->close();

return $result;