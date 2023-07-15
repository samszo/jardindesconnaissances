SELECT 
    count(distinct te.tag_id) nb,
	te.niveau + 1 - t.niveau niv,
    count(distinct te.tag_id)*(te.niveau + 1 - t.niveau) complexite

FROM
    flux_tag t
        INNER JOIN
    flux_tag te ON te.lft between t.lft and t.rgt
WHERE
    t.tag_id = 524
group by t.niveau, te.niveau