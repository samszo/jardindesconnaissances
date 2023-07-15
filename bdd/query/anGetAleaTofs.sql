SELECT 
    d.doc_id pId,
    d.titre label,
    d.url original,
    dp.titre theme,
    MIN(DATE_FORMAT(rDeb.valeur, '%Y-%m-%d')) temps,
    om.source imgFull,
    om.item_id idOmkItem,
    MIN(om.id) idOmkMedia
FROM
    flux_doc d
        INNER JOIN
    flux_rapport rDeb ON rDeb.src_id = d.parent
        AND rDeb.src_obj = 'doc'
        AND rDeb.dst_obj = 'tag'
        AND rDeb.dst_id = 4
        INNER JOIN
    flux_doc dp ON dp.doc_id = d.parent
        INNER JOIN
    omk_valarnum1.value ov ON ov.value LIKE 'flux_valarnum-flux_doc-doc_id-%'
        AND SUBSTRING(ov.value, 31) = d.doc_id
        INNER JOIN
    omk_valarnum1.media om ON om.item_id = ov.resource_id
WHERE
    d.type = 1
GROUP BY d.doc_id , om.item_id , om.source
ORDER BY RAND()
LIMIT 10;

-- order by idOmk desc
