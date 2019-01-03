SELECT 
    dI.doc_id,
--     GROUP_CONCAT(dgv1.titre) gv1titre,
--     GROUP_CONCAT(dgv1.note) gv1note,
--     GROUP_CONCAT(dgv2.titre) gv2titre,
--     GROUP_CONCAT(dgv2.note) gv2note,
--     GROUP_CONCAT(dgv3.titre) gv3titre,
--     GROUP_CONCAT(dgv3.note) gv3note,
--     GROUP_CONCAT(dgv4.titre) gv4titre,
--     GROUP_CONCAT(dgv4.note) gv4note,
--     GROUP_CONCAT(dgv5.note) gv5note
    COUNT(dgv1.doc_id) gv1nb,
    COUNT(dgv2.doc_id) gv2nb,
    COUNT(dgv3.doc_id) gv3nb,
    COUNT(dgv4.doc_id) gv4nb,
    COUNT(dgv5.doc_id) gv5nb

FROM
    flux_doc dI
        LEFT JOIN
    flux_doc dgv1 ON dgv1.parent = dI.doc_id
        AND dgv1.titre LIKE 'imagePropertiesAnnotation%'
        LEFT JOIN
    flux_doc dgv2 ON dgv2.parent = dI.doc_id
        AND dgv2.titre LIKE 'faceAnnotations%'
        LEFT JOIN
    flux_doc dgv3 ON dgv3.parent = dI.doc_id
        AND dgv3.titre LIKE 'landmarkAnnotations%'
        LEFT JOIN
    flux_doc dgv4 ON dgv4.parent = dI.doc_id
        AND dgv4.titre LIKE 'logoAnnotations%'
        LEFT JOIN
    flux_doc dgv5 ON dgv5.parent = dI.doc_id
        AND dgv5.tronc LIKE 'textAnnotations%'
WHERE
    dI.type = 1
GROUP BY dI.doc_id
ORDER BY dI.doc_id