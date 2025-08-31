const BASE = '/api';

function getCsrfToken() {
  const m = document.querySelector('meta[name="csrf-token"]');
  return m ? m.getAttribute('content') : null;
}

export default class Api {
  static async request(path, opts = {}) {
    const headers = {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      ...(opts.headers || {})
    };
    const token = getCsrfToken();
    if (token) headers['X-CSRF-TOKEN'] = token;

    const res = await fetch(path, { headers, ...opts });
    if (!res.ok) {
      const json = await res.json().catch(() => ({}));
      const err = new Error(json.message || json.error || `HTTP ${res.status}`);
      err.status = res.status; err.data = json;
      throw err;
    }
    return res.json().catch(() => ({}));
  }

  static getTicket(code) {
    return this.request(`${BASE}/tickets/${code}`);
  }

  static updateScratchProgress(playId, body) {
    return this.request(`${BASE}/plays/${playId}/scratch`, { method: 'PATCH', body: JSON.stringify(body) });
  }

  static verifyResult(playId) {
    return this.request(`${BASE}/plays/${playId}/verify`);
  }
}
