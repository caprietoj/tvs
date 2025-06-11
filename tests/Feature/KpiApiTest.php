namespace Tests\Feature;

use Tests\TestCase;

class KpiApiTest extends TestCase
{
    public function testKpiApiReturnsSuccess()
    {
        $response = $this->get('/api/kpis');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success'
        ]);
    }
}
