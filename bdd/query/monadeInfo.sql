select
m.titre mTitre
, r.rapport_id
, d.doc_id, d.titre dTitre, d.url
, t.tag_id, t.code
, u.uti_id, u.login
from flux_monade m
inner join flux_rapport r on r.monade_id = m.monade_id
inner join flux_utitagdoc utd on utd.utitagdoc_id = r.utitagdoc_id
inner join flux_doc d on d.doc_id = utd.doc_id
inner join flux_tag t on t.tag_id = utd.tag_id
inner join flux_uti u on u.uti_id = utd.uti_id
where m.monade_id = 2