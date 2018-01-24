//
//  Put this into the 'execute custom script of a vbo field that can select all snowpit profiile nodes and run something on them.
//
// <?php
//


$user = entity_load( 'user'  ,  array ($entity->uid), array() , TRUE );
//dsm($user);
if ( isset ( $user[$entity->uid]->field_professional_affiliation['und'][0]['tid'] ) ){
  $entity->field_org_ownership['und'][0]['tid'] = $user[$entity->uid]->field_professional_affiliation['und'][0]['tid'];
  entity_save( ' node' , $entity ) ;
}

/*   Similar to the previous,
 *   This script copies the professional affiliation # 1 to the default_org field. 
 *
 */


$user = entity_load( 'user'  ,  array ($entity->uid), array() , TRUE );
//dsm($user);
if ( isset ( $user[$entity->uid]->field_professional_affiliation['und'][0]['tid'] ) ){
  $entity->field_org_ownership['und'][0]['tid'] = $user[$entity->uid]->field_professional_affiliation['und'][0]['tid'];
  entity_save( ' node' , $entity ) ;
}