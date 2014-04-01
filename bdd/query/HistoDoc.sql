SELECT DATE_FORMAT(d.maj, "%Y") year, count(d.doc_id)
  FROM flux_doc d
  where DATE_FORMAT(d.maj, "%Y") != "0000"
  group by year
order by year asc