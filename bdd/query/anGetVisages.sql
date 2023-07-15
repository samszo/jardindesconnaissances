SELECT 
    d.doc_id,
    d.titre,
    d.note,
    d.parent,
    dp.titre titreParent,
    ov.resource_id,
    om.id imageId
FROM
    flux_doc d
        INNER JOIN
    flux_doc dp ON dp.doc_id = d.parent
        INNER JOIN
    omk_valarnum1.value ov ON ov.value LIKE 'flux_valarnum-flux_doc-doc_id-%'
        AND SUBSTRING(ov.value, 31) = dp.doc_id
        INNER JOIN
	omk_valarnum1.media om ON om.item_id = ov.resource_id
WHERE
    d.tronc = 'visage'
ORDER BY d.parent
