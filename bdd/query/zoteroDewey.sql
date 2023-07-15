select distinct tcode.tag_id, tcode.code, tcode.desc
-- , tcode.tag_id
-- , tdesc.code lib
-- , utd.uti_id
FROM flux_tagtag tt
inner join flux_tag tcode on tcode.tag_id = tt.tag_id_dst
-- inner join flux_tagtag ttcodedesc on ttcodedesc.tag_id_src = tcode.tag_id
-- inner join flux_tag tdesc on tdesc.tag_id = ttcodedesc.tag_id_dst
-- inner join flux_utitagdoc utd on utd.tag_id = tcode.tag_id
where tt.tag_id_src in (845, 846, 848)  -- and tcode.`desc` ='' -- and tdesc.code != ''
-- group by tcode.code
order by tcode.code