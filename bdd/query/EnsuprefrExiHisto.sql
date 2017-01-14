SELECT 
    e.exi_id,
    e.nom,
    e.prenom,
    e.data,
    COUNT(DISTINCT d.doc_id) nbDoc,
    DATE_FORMAT(d.pubDate, '%c-%y') temps,
    rq.valeur
FROM
    flux_exi e
        INNER JOIN
    flux_rapport r ON r.src_obj = 'exi' AND r.dst_obj = 'doc'
        AND r.pre_obj = 'rapport'
        AND e.exi_id = r.src_id
        INNER JOIN
    flux_doc d ON d.doc_id = r.dst_id
        INNER JOIN
    flux_rapport rq ON rq.rapport_id = r.pre_id
        INNER JOIN
    flux_monade m ON m.monade_id = rq.monade_id
WHERE
    e.data != ''
GROUP BY rq.rapport_id, e.exi_id , temps
ORDER BY rq.rapport_id, e.exi_id , d.pubDate