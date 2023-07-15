SELECT 
    d.doc_id, d.url, d.titre, SUBSTRING(d.url, 31)
FROM
    flux_rapport rFiltre
        INNER JOIN
    flux_doc d ON d.doc_id = rFiltre.src_id
WHERE
    rFiltre.dst_obj = 'tag'
        AND rFiltre.dst_obj = 'tag'
        AND rFiltre.src_obj = 'doc'
        AND rFiltre.pre_obj = 'rapport'
        AND rFiltre.dst_id = 17
        -- AND CONVERT( rFiltre.valeur , UNSIGNED) NOT BETWEEN 1800 AND 1899
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
