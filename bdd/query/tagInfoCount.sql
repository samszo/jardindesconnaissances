select t.code, t.desc , count(*) nb, count(distinct utd.uti_id) nbUti, count(distinct utd.doc_id) nbDoc
from flux_tag t
inner join flux_utitagdoc utd on utd.tag_id = t.tag_id
group by t.code
order by nb desc