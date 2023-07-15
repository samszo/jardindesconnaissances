SELECT 
    /*
    COUNT(DISTINCT d.doc_id) nbD,
    COUNT(DISTINCT dv.doc_id) nbDV,
    COUNT(DISTINCT dp.doc_id) nbDP,
    COUNT(DISTINCT dpv.doc_id) nbDVP,
    GROUP_CONCAT(DISTINCT d.doc_id) dIds,
    GROUP_CONCAT(DISTINCT dpv.doc_id) dpIds
    */
    d.doc_id,
    dpv.titre, dpv.tronc, dpv.note
FROM
    flux_doc d
        INNER JOIN
    flux_valarnum.flux_doc dp ON dp.url = d.url
        INNER JOIN
    flux_valarnum.flux_doc dpv ON dpv.parent = dp.doc_id
        AND (dpv.titre LIKE 'imagePropertiesAnnotation%'
        OR dpv.titre LIKE 'faceAnnotations%'
        OR dpv.titre LIKE 'landmarkAnnotations%'
        OR dpv.titre LIKE 'logoAnnotations%')
        LEFT JOIN
    flux_doc dv ON dv.parent = d.doc_id
        AND (dv.titre LIKE 'imagePropertiesAnnotation%'
        OR dv.titre LIKE 'faceAnnotations%'
        OR dv.titre LIKE 'landmarkAnnotations%'
        OR dv.titre LIKE 'logoAnnotations%')
WHERE
    d.type = 1 AND d.tronc != 'visage'
        AND dv.doc_id IS NULL
-- GROUP BY d.doc_id
ORDER BY d.doc_id