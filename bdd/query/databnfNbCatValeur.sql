-- select SUM(nbDoc) from (
SELECT 
    count(distinct r.src_id) nbDoc, t.code, r.valeur
FROM
    flux_tag t
    inner join flux_rapport r on t.tag_id = r.dst_id
 WHERE t.tag_id = 65 -- AND CONVERT(r.valeur,UNSIGNED) BETWEEN 1800 and 1899 
 group by r.valeur
 order by valeur 
-- order by nbDoc desc
-- ) somme
 ; 