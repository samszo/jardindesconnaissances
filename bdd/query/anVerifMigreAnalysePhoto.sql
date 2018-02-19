SELECT 
    COUNT(DISTINCT d.doc_id) nbD,
    SUBSTRING(dv.titre, 1, 10),
    COUNT(DISTINCT dv.doc_id) nbDv,
    COUNT(DISTINCT dp.doc_id) nbDp,
    COUNT(DISTINCT dvP.doc_id) nbDvP
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
        INNER JOIN
    flux_valarnum_prod1_1.flux_doc dvP ON dvP.parent = dp.doc_id
        AND (dvP.titre LIKE 'imagePropertiesAnnotation%'
        OR dvP.titre LIKE 'faceAnnotations%'
        OR dvP.titre LIKE 'landmarkAnnotations%'
        OR dvP.titre LIKE 'logoAnnotations%')
GROUP BY SUBSTRING(dv.titre, 1, 10)
ORDER BY d.doc_id