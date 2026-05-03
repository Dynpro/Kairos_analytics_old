import json, urllib.parse, urllib.request
cfg = {
    "type": "bar",
    "data": {
        "labels": ["2022", "2023", "2024"],
        "datasets": [
            {"type": "bar", "label": "EMPLOYEE - N", "data": [1618, 22, 18], "backgroundColor": "#2B3A5A"},
            {"type": "bar", "label": "EMPLOYEE - AVERAGE AGE", "data": [48.5, 44.9, 44.3], "backgroundColor": "#5A9AD4"},
            {"type": "line", "label": "DEPENDENT - N", "data": [65, 38, 52], "borderColor": "#5A9AD4", "fill": False},
            {"type": "line", "label": "DEPENDENT - AVERAGE AGE", "data": [5.0, 5.7, 5.3], "borderColor": "#888", "fill": False}
        ]
    },
    "options": {
        "indexAxis": "y",
        "plugins": {
            "datalabels": {"display": True, "align": "right", "anchor": "end", "font": {"weight": "bold", "size": 9}},
            "legend": {"position": "bottom"}
        }
    }
}
s = json.dumps(cfg, separators=(",",":"))
url = f"https://quickchart.io/chart?c={urllib.parse.quote(s, safe='')}&w=700&h=350&f=png&v=3"
with urllib.request.urlopen(url) as resp:
    with open("test_chart_1.png", "wb") as f:
        f.write(resp.read())
print("Chart generated")
