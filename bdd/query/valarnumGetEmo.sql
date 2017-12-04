select 
-- r.rapport_id, r.maj, r.niveau, r.valeur 
-- , r.*
SUM(r.niveau) value
, GROUP_CONCAT(u.uti_id) utis
, t.tag_id 'key', t.code 'type'
, GROUP_CONCAT(d.doc_id) docs
-- , dp.doc_id, dp.url
-- , dgp.doc_id, dgp.titre
-- ,	DATE_FORMAT(r.maj, '%Y-%m-%d %H') temps
-- ,	MIN(UNIX_TIMESTAMP(r.maj)) MinDate
-- ,	MAX(UNIX_TIMESTAMP(r.maj)) MaxDate
,	DATE_FORMAT(dgprDeb.valeur, '%Y-%m-%d') temps
,	MIN(UNIX_TIMESTAMP(DATE(dgprDeb.valeur))) MinDate
,	MAX(UNIX_TIMESTAMP(DATE(dgprDeb.valeur))) MaxDate
from flux_rapport r
inner join flux_doc d on d.doc_id = r.src_id
 inner join flux_doc dp on dp.doc_id = d.parent
 inner join flux_doc dgp on dgp.doc_id = dp.parent
 inner join flux_rapport dgprDeb on dgprDeb.src_id = dgp.doc_id AND dgprDeb.src_obj = 'doc' AND dgprDeb.dst_id = 4 AND dgprDeb.dst_obj = 'tag'
-- inner join flux_rapport dgprFin on dgprFin.src_id = dgp.doc_id AND dgprFin.src_obj = 'doc' AND dgprFin.dst_id = 4 AND dgprFin.dst_obj = 'tag'
 
inner join flux_uti u on u.uti_id = r.pre_id
inner join flux_tag t on t.tag_id = r.dst_id
where r.monade_id = 3
GROUP BY t.tag_id, temps
ORDER BY temps
