SELECT m.monade_id, m.titre
, rq.rapport_id
, dq.doc_id dqid, dq.titre dqtitre
-- , tq.tag_id, tq.code
, dd.doc_id ddid, dd.titre ddtitre
 , ur.uti_id, ur.login
 , ar.acti_id, ar.code acti
 , re.niveau, re.valeur, re.maj
 , te.tag_id, te.code
FROM flux_monade m
-- donnée de la question
INNER JOIN flux_rapport rq ON rq.monade_id = m.monade_id AND rq.src_obj = "doc" AND rq.pre_obj = "tag" AND rq.dst_obj = "doc"
INNER JOIN flux_doc dq ON rq.src_id = dq.doc_id
INNER JOIN flux_doc dpq ON dq.parent = dpq.doc_id AND dpq.titre = "questions"
-- INNER JOIN flux_tag tq ON rq.pre_id = tq.tag_id 
INNER JOIN flux_doc dd ON rq.dst_id = dd.doc_id
-- donnée de la réponse
 INNER JOIN flux_rapport rr ON rr.src_id = rq.rapport_id AND rr.src_obj = "rapport" AND rr.pre_obj = "uti" AND rr.dst_obj = "acti"
 INNER JOIN flux_uti ur ON rr.pre_id = ur.uti_id
 INNER JOIN flux_acti ar ON rr.dst_id = ar.acti_id
 INNER JOIN flux_rapport re ON re.pre_id = rr.rapport_id AND re.src_obj = "tag" AND re.pre_obj = "rapport" AND re.dst_obj = "doc"
 INNER JOIN flux_tag te ON re.src_id = te.tag_id 
 
