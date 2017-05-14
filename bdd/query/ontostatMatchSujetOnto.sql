SELECT 
count(*) nb, 
vS.value, group_concat(DISTINCT vS.resource_id) ids, 
vC.value, vC.resource_id 
FROM value vS 
inner join value vC on vC.value LIKE CONCAT('%',vS.value,'%') AND vC.property_id IN (1,4,17,19,117)
 inner join resource r on r.id = vC.resource_id AND r.resource_type = 'Omeka\\Entity\\ItemSet'
WHERE vS.property_id = 3 
 group by vS.value
 ORDER BY vS.value