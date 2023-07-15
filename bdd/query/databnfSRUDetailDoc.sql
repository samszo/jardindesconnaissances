select 
d.*
, t.code, t.tag_id
, r.valeur
from flux_doc d
inner join flux_rapport r on  r.src_id = d.doc_id AND r.src_obj = 'doc' AND r.dst_obj = 'tag'
inner join flux_tag t on r.dst_id = t.tag_id
WHERE r.src_id = 18849
group by t.code 
