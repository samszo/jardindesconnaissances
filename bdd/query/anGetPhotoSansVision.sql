SELECT 
    COUNT(DISTINCT d.doc_id) nbD
    , COUNT(DISTINCT dv.doc_id) nbDV
    -- d.url
    , SUBSTRING(dv.titre, 1, 10) 
FROM
    flux_doc d
        INNER JOIN
    flux_doc dv ON dv.parent = d.doc_id
        AND (dv.titre LIKE 'imagePropertiesAnnotation%'
        OR dv.titre LIKE 'faceAnnotations%'
        OR dv.titre LIKE 'landmarkAnnotations%'
        OR dv.titre LIKE 'logoAnnotations%')
WHERE d.type = 1 AND d.tronc != 'visage' -- AND dv.doc_id is null
group by SUBSTRING(dv.titre, 1, 10) 
ORDER BY d.doc_id