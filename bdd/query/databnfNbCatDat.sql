SELECT 
    count(distinct r.src_id) nbDoc, t.code, t.tag_id
FROM
    flux_tag t
    inner join flux_rapport r on t.tag_id = r.dst_id
 
	-- inner join flux_rapport rFiltre on rFiltre.pre_id = r.pre_id and rFiltre.dst_id = 62 AND CONVERT(rFiltre.valeur,UNSIGNED) BETWEEN 1800 and 1899 

 WHERE t.parent = 55 
-- WHERE t.tag_id = 61 

 
 group by t.tag_id
 order by nbDoc desc; 