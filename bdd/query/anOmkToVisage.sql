/*
UPDATE flux_visage v
        INNER JOIN
    omk_valarnum1.value ov ON ov.value LIKE 'flux_valarnum-flux_doc-doc_id-%'
        AND SUBSTRING(ov.value, 31) = v.doc_id
        INNER JOIN
    omk_valarnum1.media om ON om.item_id = ov.resource_id 
SET 
    v.url = CONCAT('http://gapai.univ-paris8.fr/ValArNum/omks/files/original/',
            om.storage_id,
            '.',
            om.extension),
    v.source = om.source
    */

SELECT 
    d.doc_id,
    d.url,
    d.titre,
    d.note,
    d.parent,
    om.source,
    CONCAT('http://gapai.univ-paris8.fr/ValArNum/omks/files/original/',
            om.storage_id,
            '.',
            om.extension) oUrl
FROM
    flux_doc d
        INNER JOIN
    flux_visage v ON v.doc_id = d.doc_id
        INNER JOIN
    omk_valarnum1.value ov ON ov.value LIKE 'flux_valarnum-flux_doc-doc_id-%'
        AND SUBSTRING(ov.value, 31) = d.doc_id
        INNER JOIN
    omk_valarnum1.media om ON om.item_id = ov.resource_id
WHERE
    d.tronc = 'visage'
