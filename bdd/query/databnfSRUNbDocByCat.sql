select t.code, t.tag_id
, count(DISTINCT r.src_id) nbDoc
from flux_tag t
inner join flux_rapport r on  r.dst_id = t.tag_id AND r.src_obj = 'doc' AND r.dst_obj = 'tag'
group by t.code 