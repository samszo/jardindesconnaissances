select d.doc_id
-- , dp.url
, rL.x, rL.y 
, rR.x, rR.y 
, rL.x - rR.x distance
from flux_doc d
-- inner join flux_doc dp on dp.doc_id = d.parent
inner join flux_repere rL on rL.doc_id = d.doc_id and rL.type = 'LEFT_EYE' 
inner join flux_repere rR on rR.doc_id = d.doc_id and rR.type = 'RIGHT_EYE' 
order by distance
