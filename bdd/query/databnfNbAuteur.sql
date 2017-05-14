SELECT
--	r.*,
--    d.*,
--	rD.*,
    COUNT(distinct r.src_id) nbDoc, 
    r.valeur auteur
--    ,group_concat(rProp1.valeur) Years

FROM
    flux_rapport r
    INNER JOIN flux_doc d ON d.doc_id = r.src_id
    inner join flux_rapport rD on rD.pre_id = r.rapport_id AND rD.pre_obj = 'rapport'
--    inner join flux_rapport rD on rD.src_id = d.doc_id AND rD.src_obj = 'doc' and rD.dst_obj='acti' and rD.dst_id = 4
--    inner join flux_rapport rProp1 on rProp1.pre_id = rD.rapport_id
--    inner join flux_tag t1 on t1.tag_id = rProp1.dst_id and t1.tag_id = 62 

WHERE
    r.dst_id = 2 AND r.dst_obj = 'tag'
        AND r.valeur NOT LIKE '%Voir les notices liées en tant%'
 GROUP BY r.valeur
 order by nbDoc desc