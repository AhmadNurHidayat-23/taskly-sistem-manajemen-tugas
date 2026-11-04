"""Exercise the packaged web application, including its cookies and SQLite volume."""

import http.cookiejar
import json
import re
import sys
from html.parser import HTMLParser
from pathlib import Path
from urllib.error import HTTPError
from urllib.parse import urlencode, urljoin
from urllib.request import HTTPCookieProcessor, Request, build_opener


class HiddenInputs(HTMLParser):
    def __init__(self, html):
        super().__init__()
        self.values = {}
        self.feed(html)

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag == "input" and attrs.get("type") == "hidden":
            self.values[attrs.get("name")] = attrs.get("value", "")


phase, base_url, state_file = sys.argv[1:]
opener = build_opener(HTTPCookieProcessor(http.cookiejar.CookieJar()))


def get(path, expected=200):
    try:
        with opener.open(urljoin(base_url, path), timeout=10) as response:
            assert response.status == expected
            return response.read().decode(), response.url
    except HTTPError as error:
        assert error.code == expected, f"GET {path}: {error.code}, expected {expected}"
        return error.read().decode(), error.url


def token(path):
    html, _ = get(path)
    value = HiddenInputs(html).values.get("_token")
    assert value, f"CSRF token missing on {path}"
    return value


def post(path, data, expected=200):
    request = Request(urljoin(base_url, path), data=urlencode(data).encode(), method="POST")
    try:
        with opener.open(request, timeout=10) as response:
            assert response.status == expected
            return response.read().decode(), response.url
    except HTTPError as error:
        assert error.code == expected, f"POST {path}: {error.code}, expected {expected}"
        return error.read().decode(), error.url


task = {"title": "Taskly Docker smoke", "description": "Persisted across containers",
        "priority": "high", "status": "todo", "due_date": ""}

if phase == "create":
    html, _ = get("/")
    assert "Taskly" in html
    assets = re.findall(r'(?:href|src)="([^"]*/build/assets/[^"?#]+\.(?:css|js))', html)
    assert any(asset.endswith(".css") for asset in assets), "Built CSS missing"
    assert any(asset.endswith(".js") for asset in assets), "Built JavaScript missing"
    for asset in assets:
        content, _ = get(asset)
        assert content.strip(), f"Empty asset: {asset}"
    csrf = token("/tasks/create")
    post("/tasks", task, expected=419)
    html, location = post("/tasks", task | {"_token": csrf})
    match = re.search(r"/tasks/(\d+)$", location)
    assert match and task["title"] in html, "Task creation failed"
    Path(state_file).write_text(json.dumps({"id": match[1]}))
elif phase == "verify":
    task_id = json.loads(Path(state_file).read_text())["id"]
    html, _ = get(f"/tasks/{task_id}")
    assert task["title"] in html, "Task lost after container recreation"
    updated = task | {"title": "Updated from Docker", "_method": "PUT",
                      "_token": token(f"/tasks/{task_id}/edit")}
    html, _ = post(f"/tasks/{task_id}", updated)
    assert updated["title"] in html, "Task update failed"
    post(f"/tasks/{task_id}/status", {"status": "completed", "_method": "PATCH",
                                    "_token": token(f"/tasks/{task_id}")})
    html, _ = get("/completed")
    assert updated["title"] in html, "Status update failed"
    post(f"/tasks/{task_id}", {"_method": "DELETE", "_token": token(f"/tasks/{task_id}")})
    get(f"/tasks/{task_id}", expected=404)
    get("/.env", expected=404)
    get("/composer.json", expected=404)
else:
    raise ValueError("Unknown smoke test phase")
