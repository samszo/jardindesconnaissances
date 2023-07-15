SELECT 
    d.doc_id,
    d.url,
    d.titre,
    COUNT(DISTINCT dv.doc_id) nbDv,
    GROUP_CONCAT(DISTINCT dv.doc_id) dvIds,
    COUNT(DISTINCT dp.doc_id) nbDp,
    GROUP_CONCAT(DISTINCT dp.doc_id) dpIds
FROM
    flux_doc d
        INNER JOIN
    flux_doc dv ON dv.parent = d.doc_id
        AND (dv.titre LIKE 'imagePropertiesAnnotation%'
        OR dv.titre LIKE 'faceAnnotations%'
        OR dv.titre LIKE 'landmarkAnnotations%'
        OR dv.titre LIKE 'logoAnnotations%')
        INNER JOIN
    flux_valarnum_prod1_1.flux_doc dp ON dp.url = d.url
GROUP BY d.doc_id
ORDER BY d.doc_id