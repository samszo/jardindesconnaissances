SELECT 
    d.doc_id,
    d.url,
    d.titre,
    d.note,
    dd.doc_id idDwld,
    di.doc_id idI,
    di.url urlI,
    di.titre titreI,
    di.note noteI,
    rA.dst_id,
    e.nom,
    rD.valeur dateA,
    rS.valeur siecleA,
    rT.valeur typeA,
	GROUP_CONCAT(DISTINCT dgv1.titre SEPARATOR '#') gv1titre,
    GROUP_CONCAT(DISTINCT dgv1.note SEPARATOR '#') gv1note,
	GROUP_CONCAT(DISTINCT dgv2.titre SEPARATOR '#') gv2titre,
	GROUP_CONCAT(DISTINCT dgv2.note SEPARATOR '#') gv2note,
    GROUP_CONCAT(DISTINCT dgv3.titre SEPARATOR '#') gv3titre,
	GROUP_CONCAT(DISTINCT dgv3.note SEPARATOR '#') gv3note,
	GROUP_CONCAT(DISTINCT dgv4.titre SEPARATOR '#')  gv4titre,
	GROUP_CONCAT(DISTINCT dgv4.note SEPARATOR '#') gv4note,
	GROUP_CONCAT(DISTINCT dgv5.note SEPARATOR '#') gv5note
FROM
    flux_doc d
        INNER JOIN
    flux_doc dd ON dd.parent = d.doc_id
        INNER JOIN
    flux_doc di ON di.parent = dd.doc_id
        LEFT JOIN
    flux_rapport rA ON rA.src_id = dd.doc_id
        AND rA.src_obj = 'doc'
        AND rA.dst_obj = 'exi'
        AND rA.pre_obj = 'tag'
        LEFT JOIN
    flux_exi e ON e.exi_id = rA.dst_id
        LEFT JOIN
    flux_rapport rD ON rD.src_id = dd.doc_id
        AND rD.src_obj = 'doc'
        AND rD.dst_obj = 'tag'
        AND rD.dst_id = 4
        LEFT JOIN
    flux_rapport rS ON rS.src_id = dd.doc_id
        AND rS.src_obj = 'doc'
        AND rS.dst_obj = 'tag'
        AND rD.dst_id = 5
        LEFT JOIN
    flux_rapport rT ON rT.src_id = dd.doc_id
        AND rT.src_obj = 'doc'
        AND rT.dst_obj = 'tag'
        AND rT.dst_id = 3
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
    d.tronc = 'artefact' AND d.doc_id = 1301
GROUP BY d.doc_id -- , dgv1.doc_id, dgv2.doc_id, dgv3.doc_id, dgv4.doc_id