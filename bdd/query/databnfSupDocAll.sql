SELECT 
    d.doc_id ,d.url, d.titre, SUBSTRING(d.url,31)
FROM flux_doc d	 
WHERE d.url LIKE 'http://data.bnf.fr/ark:/12148/%'
AND d.doc_id not in (
SELECT 
    d.doc_id
FROM flux_doc d	 
	inner join  flux_rapport rFiltre on d.doc_id = rFiltre.src_id
AND rFiltre.dst_obj = 'tag' and rFiltre.dst_id = 62 AND CONVERT(rFiltre.valeur,UNSIGNED) BETWEEN 1800 and 1899 
);
--  group by rFiltre.src_id
-- order by nbDoc desc
; 

/*
SELECT 
    r.*,
    rS.*
FROM
    flux_tag t
    inner join flux_rapport r on t.tag_id = r.dst_id  AND r.dst_obj = 'tag'
    inner join flux_rapport rS on rS.rapport_id = r.pre_id
 WHERE t.tag_id = 62 AND CONVERT(r.valeur,UNSIGNED) NOT BETWEEN 1800 and 1899 
 */
