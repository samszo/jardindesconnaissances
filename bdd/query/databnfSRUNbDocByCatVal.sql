select t.code, t.tag_id
, r.valeur
, count(r.src_id) nbDoc
from flux_tag t
inner join flux_rapport r on  r.dst_id = t.tag_id AND r.src_obj = 'doc' AND r.dst_obj = 'tag'
where t.tag_id = 7
group by r.valeur 
order by nbDoc desc