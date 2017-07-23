SELECT 
    d.doc_id,
    d.titre,
    d.url,
    d.tronc,
    d.niveau,
    d.parent,
    rTd.valeur 'debut',
    rTf.valeur 'fin'
    /*
    ,g.adresse
    ,e.nom, e.prenom, e.nait, e.mort
    */
FROM
    flux_doc d
        LEFT JOIN
    flux_rapport rTd ON rTd.src_id = d.doc_id
        AND rTd.src_obj = 'doc'
        AND rTd.dst_obj = 'tag'
        AND rTd.dst_id = 4
        LEFT JOIN
    flux_rapport rTf ON rTf.src_id = d.doc_id
        AND rTf.src_obj = 'doc'
        AND rTf.dst_obj = 'tag'
        AND rTf.dst_id = 5
        /*
        LEFT JOIN
    flux_rapport rG ON rG.src_id = d.doc_id
        AND rG.src_obj = 'doc'
        AND rG.dst_obj = 'geo'
        LEFT JOIN
    flux_geo g ON g.geo_id = rG.dst_id
        LEFT JOIN
    flux_rapport rE ON rE.src_id = d.doc_id
        AND rE.src_obj = 'doc'
        AND rE.dst_obj = 'exi'
        LEFT JOIN
    flux_exi e ON e.exi_id = rE.dst_id
    */
 WHERE  d.doc_id=22
 -- d.niveau = 3
-- GROUP BY d.doc_id
ORDER BY d.doc_id
