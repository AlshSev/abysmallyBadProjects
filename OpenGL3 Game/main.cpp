#include <glm/glm.hpp>
#include <glm/gtc/matrix_transform.hpp>
#include <glm/gtc/type_ptr.hpp>
#include <GL/glew.h>
#include <GLFW/glfw3.h>
#include <FreeImage.h>
#include <iostream>
#include <chrono>

using namespace glm;

const char *vertexShaderSource = "#version 330 core\n"
    "layout (location = 0) in vec3 pos;\n"
    "layout (location = 1) in vec3 color;\n"
    "uniform mat4 projection;\n"
    "uniform mat4 view;\n"
    "uniform mat4 model;\n"
    "out vec3 fragment_color;\n"
    "out vec3 fragment_pos;\n"
    "out vec4 real_pos;\n"
    "out mat4 pvm;\n"
    "void main() {\n"
    "   fragment_color = color;\n"
    "   fragment_pos = pos;\n"
    "   pvm = projection * view * model;\n"
    "   gl_Position = pvm * vec4(pos, 1);\n"
    "   real_pos = gl_Position;\n"
    "}\0";

const char *fragmentShaderSource = "#version 330 core\n"
    "in vec3 fragment_color;\n"
    "in vec3 fragment_pos;\n"
    "in vec4 real_pos;\n"
    "in mat4 pvm;\n"
    "out vec4 FragColor;\n"
    "uniform bool wave;\n"
    "uniform bool gui;\n"
    "uniform int delta;\n"
    "const vec3 light_dir = vec3(0.1, 0.3, 0.5);\n"
    "const vec3 light_color = vec3(0.5, 0.5, 0.5);\n"
    "const vec3 light_ambient = vec3(0.1, 0.1, 0);\n"
    "uniform sampler2D tex;\n"
    "const float ampl = 0.1;\n"
    "const float speed = 0.001;\n"
    "const float k = 10;\n"
    "void main() {\n"
    "   if (gui)\n"
    "       FragColor = texture(tex, (fragment_pos.xy + vec2(1, -1)) / 0.15);\n"
    "   else if (wave) {\n"
    "       float x = fragment_pos.x;\n"
    "       float y_change = x * ampl * sin(k * (speed * delta + x));\n"
    "       float y = fragment_pos.y + y_change;\n"
    "       if (abs(y - 0.3) <= 0.2 && abs(x + 0.3) <= 0.3) {\n"
    "           vec4 tex_color = texture(tex, vec2((x + 0.6) / 0.6, (y - 0.1) * 2.5));\n"
    "           float nx = y_change / ampl / x * -sqrt(2) / 2;\n"
    "           vec3 normal = vec3(nx, 0, sqrt(1.0 - nx * nx));\n"
    "           normal = normalize(normal);\n"
    "           vec3 light_dirN = normalize(light_dir);\n"
    "           float diff = max(dot(normal, light_dirN), 0);\n"
    "           vec3 diffuse = light_color * diff;\n"
    "           FragColor = vec4((tex_color.rgb * (diffuse + light_ambient)), tex_color.a);\n"
    "       } else\n"
    "           FragColor = vec4(0);\n"
    "   } else\n"
    "       FragColor = vec4(fragment_color, 1.0f);\n"
    "}\0";

using namespace std::chrono_literals;
auto start = std::chrono::steady_clock::now();
unsigned long long delta = 0, prev = 0, elapsed = 0;

bool wireframe = false;
auto prev_L = GLFW_RELEASE;
float side_speed = 0.002;
const float base_forward_speed = 0.001;
float forward_speed = base_forward_speed;
float speed_increase = 0.001;
void processInput(GLFWwindow *window, vec3 &camera) {
    if (glfwGetKey(window, GLFW_KEY_ESCAPE) == GLFW_PRESS)
        glfwSetWindowShouldClose(window, true);
    auto l_state = glfwGetKey(window, GLFW_KEY_L);
    if (l_state == GLFW_PRESS && prev_L == GLFW_RELEASE) {
        wireframe = !wireframe;
        if (wireframe)
            glPolygonMode(GL_FRONT_AND_BACK, GL_LINE);
        else
            glPolygonMode(GL_FRONT_AND_BACK, GL_FILL);
    }
    prev_L = l_state;
    if (glfwGetKey(window, GLFW_KEY_RIGHT) == GLFW_PRESS)
        camera.x -= side_speed * delta;
    if (glfwGetKey(window, GLFW_KEY_LEFT) == GLFW_PRESS)
        camera.x += side_speed * delta;
    if (abs(camera.x) > 2)
        camera.x = camera.x / abs(camera.x) * 2;
    // camera.z = sin(forward_speed * elapsed) * 2 - 2;
    // if (glfwGetKey(window, GLFW_KEY_DOWN) == GLFW_PRESS)
    //     camera.z -= 0.01;
    // if (glfwGetKey(window, GLFW_KEY_UP) == GLFW_PRESS)
    //     camera.z += 0.01;
}

int main() {
    std::srand(start.time_since_epoch() / 1ms);
    glfwInit();

    int width = 1400, height = 800;

    glfwWindowHint(GLFW_CONTEXT_VERSION_MAJOR, 3);
    glfwWindowHint(GLFW_CONTEXT_VERSION_MINOR, 3);
    glfwWindowHint(GLFW_OPENGL_PROFILE, GLFW_OPENGL_CORE_PROFILE);
    GLFWwindow* window = glfwCreateWindow(width, height, "Slalom", NULL, NULL);
    if (window == NULL) {
        std::cout << "Failed to create GLFW window" << std::endl;
        glfwTerminate();
        return -1;
    }
    glfwMakeContextCurrent(window);
    glfwSetFramebufferSizeCallback(window, [] ([[maybe_unused]] GLFWwindow* window, int w, int h) {
        glViewport(0, 0, w, h);
    });
    glViewport(0, 0, width, height);

    glewInit();

    glEnable(GL_DEPTH_TEST);
    glDepthFunc(GL_LESS);
    glEnable(GL_BLEND);
    glBlendFunc(GL_SRC_ALPHA, GL_ONE_MINUS_SRC_ALPHA);

    unsigned int shaderProgram;
    {
        unsigned int vertexShader;
        vertexShader = glCreateShader(GL_VERTEX_SHADER);
        glShaderSource(vertexShader, 1, &vertexShaderSource, NULL);
        glCompileShader(vertexShader);

        int  success;
        char infoLog[512];
        glGetShaderiv(vertexShader, GL_COMPILE_STATUS, &success);
        if (!success)
        {
            glGetShaderInfoLog(vertexShader, 512, NULL, infoLog);
            std::cout << "ERROR::SHADER::VERTEX::COMPILATION_FAILED\n" << infoLog << std::endl;
        }

        unsigned int fragmentShader;
        fragmentShader = glCreateShader(GL_FRAGMENT_SHADER);
        glShaderSource(fragmentShader, 1, &fragmentShaderSource, NULL);
        glCompileShader(fragmentShader);

        glGetShaderiv(fragmentShader, GL_COMPILE_STATUS, &success);
        if (!success)
        {
            glGetShaderInfoLog(fragmentShader, 512, NULL, infoLog);
            std::cout << "ERROR::SHADER::FRAGMENT::COMPILATION_FAILED\n" << infoLog << std::endl;
        }

        shaderProgram = glCreateProgram();
        glAttachShader(shaderProgram, vertexShader);
        glAttachShader(shaderProgram, fragmentShader);
        glLinkProgram(shaderProgram);

        glDeleteShader(vertexShader);
        glDeleteShader(fragmentShader);
    }
    glUseProgram(shaderProgram);

    unsigned int div_amount = 6;
    unsigned int wrapper_vertex_amount = 4 + div_amount * 2;
    unsigned int vertex_amount = wrapper_vertex_amount + 6 + 6 + 12;
    float vertices[vertex_amount * 3];
    float vertex_colors[vertex_amount * 3];

    float colors[][3] = {
        {1, 0, 0},
        {0, 1, 0},
        {0, 0, 1},
        {0, 1, 1},
        {1, 0, 1},
        {1, 1, 0},
        {0.6, 0.4, 0.3},
        {1, 1, 1},
        {0, 0, 1},
        {0, 1, 0}
    };

    int pos = 0;

    auto fill = [&] (float x, float y, float z, int color) {
        vertex_colors[pos] = colors[color][0];
        vertices[pos++] = x;
        vertex_colors[pos] = colors[color][1];
        vertices[pos++] = y;
        vertex_colors[pos] = colors[color][2];
        vertices[pos++] = z;
    };
    const float angle_delta = atan2(-1, 0) * 4 / div_amount;
    const float radius = 0.015;
    fill(0, 0.5, radius, 6);
    fill(0, -0.5, radius, 6);
    for (unsigned int i = 0; i < div_amount; ++i) {
        fill(sin(angle_delta * i) * radius, 0.5, cos(angle_delta * i) * radius, 6);
        fill(sin(angle_delta * i) * radius, -0.5, cos(angle_delta * i) * radius, 6);
    }
    fill(0, 0.5, radius, 6);
    fill(0, -0.5, radius, 6);
    //flag
    fill(-0.6, 0.6, 0, 7);
    fill(0, 0.6, 0, 7);
    fill(-0.6, 0, 0, 7);
    fill(0, 0.6, 0, 7);
    fill(-0.6, 0, 0, 7);
    fill(0, 0, 0, 7);

    int heart_pos = pos / 3;
    float heart_w = 0.15;
    float heart_h = 0.15;
    fill(-1, 1, 0, 0);
    fill(-1 + heart_w, 1, 0, 0);
    fill(-1, 1 - heart_h, 0, 0);
    fill(-1 + heart_w, 1, 0, 0);
    fill(-1, 1 - heart_h, 0, 0);
    fill(-1 + heart_w, 1 - heart_h, 0, 0);

    int env_pos = pos / 3;
    fill(-1, 1, 0, 8);
    fill(1, 1, 0, 8);
    fill(-1, 0, 0, 8);
    fill(1, 1, 0, 8);
    fill(1, 0, 0, 8);
    fill(-1, 0, 0, 8);

    fill(-1, -1, 0, 9);
    fill(1, -1, 0, 9);
    fill(-1, 0, 0, 9);;
    fill(1, -1, 0, 9);
    fill(-1, 0, 0, 9);
    fill(1, 0, 0, 9);


    unsigned int VAO;
    {
        glGenVertexArrays(1, &VAO);
        glBindVertexArray(VAO);

        unsigned int vertex_buffer;
        glGenBuffers(1, &vertex_buffer);
        glBindBuffer(GL_ARRAY_BUFFER, vertex_buffer);
        glBufferData(GL_ARRAY_BUFFER, sizeof(vertices), vertices, GL_STATIC_DRAW);
        glVertexAttribPointer(0, 3, GL_FLOAT, GL_FALSE, 3 * sizeof(float), (void*)0);
        glEnableVertexAttribArray(0);

        unsigned int color_buffer;
        glGenBuffers(1, &color_buffer);
        glBindBuffer(GL_ARRAY_BUFFER, color_buffer);
        glBufferData(GL_ARRAY_BUFFER, sizeof(vertex_colors), vertex_colors, GL_STATIC_DRAW);
        glVertexAttribPointer(1, 3, GL_FLOAT, GL_FALSE, 3 * sizeof(float), (void*)0);
        glEnableVertexAttribArray(1);

        glBindVertexArray(0);
    }

    auto random_one = [] (float low, float high) -> float {
        return low + (float)rand() / RAND_MAX * (high - low);
    };

    int uniform_projection = glGetUniformLocation(shaderProgram, "projection");
    int uniform_view = glGetUniformLocation(shaderProgram, "view");
    int uniform_model = glGetUniformLocation(shaderProgram, "model");
    int uniform_wave = glGetUniformLocation(shaderProgram, "wave");
    int uniform_delta = glGetUniformLocation(shaderProgram, "delta");
    int uniform_gui = glGetUniformLocation(shaderProgram, "gui");
    int unifrom_light_pos = glGetUniformLocation(shaderProgram, "light_pos");

    mat4 view = mat4(1);
    vec3 camera = vec3(0, -0.25, 0);
    mat4 flag_model1 = mat4(1), flag_model2 = translate(mat4(1), vec3(0, 0, 5));

    auto gen_new_flag_pos = [&] () -> void {
        flag_model1 = flag_model2;
        flag_model2 = translate(mat4(1), vec3(random_one(-1.5, 1.5), 0, random_one(6, 9) + flag_model1[3][2]));
    };
    gen_new_flag_pos();

    //textures
    unsigned int tex_flag, tex_heart;
    {
        glGenTextures(1, &tex_flag);
        glBindTexture(GL_TEXTURE_2D, tex_flag);
        const char *tex_path = "textures\\flag.jpg\0";
        FIBITMAP* texture = FreeImage_Load(FreeImage_GetFileType(tex_path, 0), tex_path);
        int tex_w = FreeImage_GetWidth(texture);
        int tex_h = FreeImage_GetHeight(texture);
        glTexImage2D(GL_TEXTURE_2D, 0, GL_RGB, tex_w, tex_h, 0, GL_BGR, GL_UNSIGNED_BYTE, (void*)FreeImage_GetBits(texture));
        glGenerateMipmap(GL_TEXTURE_2D);
        FreeImage_Unload(texture);

        glGenTextures(1, &tex_heart);
        glBindTexture(GL_TEXTURE_2D, tex_heart);
        const char *tex2_path = "textures\\heart.png\0";
        FIBITMAP* texture2 = FreeImage_Load(FreeImage_GetFileType(tex2_path, 0), tex2_path);
        tex_w = FreeImage_GetWidth(texture2);
        tex_h = FreeImage_GetHeight(texture2);
        glTexImage2D(GL_TEXTURE_2D, 0, GL_RGBA, tex_w, tex_h, 0, GL_BGRA, GL_UNSIGNED_BYTE, (void*)FreeImage_GetBits(texture2));
        glGenerateMipmap(GL_TEXTURE_2D);
        FreeImage_Unload(texture2);
    }

    const int lost_timeout = 5000;
    int missed = 0, claimed = 0, heart_left = 3, lost_accum = 0;
    bool lost = false;
    while(!glfwWindowShouldClose(window)) {
        prev = elapsed;
        elapsed = (std::chrono::steady_clock::now() - start) / 1ms;
        delta = elapsed - prev;
        processInput(window, camera);
        camera.z += forward_speed * delta;


        if (lost) {
            lost_accum += delta;
            if (lost_accum >= lost_timeout)
                glfwSetWindowShouldClose(window, true);
        }

        //collision handling
        if (flag_model1[3][2] + 0.5 < camera.z) {
            gen_new_flag_pos();
            ++missed;
            --heart_left;
            if (heart_left == 0) {
                lost = true;
                side_speed = 0;
                forward_speed = 0;
            }
        } else if (abs(flag_model1[3][0] - camera.x) < 0.3 && flag_model1[3][2] - 0.6 < camera.z) {
            gen_new_flag_pos();
            ++claimed;
            forward_speed = base_forward_speed + speed_increase * std::sqrt(claimed);
        }

        glClearColor(0, 0, 0, 1);
        glClear(GL_COLOR_BUFFER_BIT | GL_DEPTH_BUFFER_BIT);

        glBindVertexArray(VAO);

        //env
        glDisable(GL_DEPTH_TEST);
        mat4 identity4 = mat4(1);
        glUniformMatrix4fv(uniform_model, 1, GL_FALSE, &identity4[0][0]);
        glUniformMatrix4fv(uniform_view, 1, GL_FALSE, &identity4[0][0]);
        glUniformMatrix4fv(uniform_projection, 1, GL_FALSE, &identity4[0][0]);
        glDrawArrays(GL_TRIANGLES, env_pos, 12);
        glEnable(GL_DEPTH_TEST);

        glfwGetWindowSize(window, &width, &height);
        mat4 projection = perspective(radians(60.0f), (float)width / (float)height, 0.1f, 100.0f);
        glUniformMatrix4fv(uniform_projection, 1, GL_FALSE, &projection[0][0]);

        view = lookAt(camera, camera + vec3(0, 0, 1), vec3(0, 1, 0));
        glUniformMatrix4fv(uniform_view, 1, GL_FALSE, &view[0][0]);

        glUniform3f(unifrom_light_pos, camera.x, camera.y, camera.z);

        //second flag
        glUniformMatrix4fv(uniform_model, 1, GL_FALSE, &flag_model2[0][0]);
        glUniform1i(uniform_wave, 0);
        glDrawArrays(GL_TRIANGLE_STRIP, 0, wrapper_vertex_amount);
        glUniform1i(uniform_wave, 1);
        glUniform1i(uniform_delta, elapsed);
        glBindTexture(GL_TEXTURE_2D, tex_flag);
        glDrawArrays(GL_TRIANGLES, wrapper_vertex_amount, 6);

        //first flag
        glUniformMatrix4fv(uniform_model, 1, GL_FALSE, &flag_model1[0][0]);
        glUniform1i(uniform_wave, 0);        
        glDrawArrays(GL_TRIANGLE_STRIP, 0, wrapper_vertex_amount);
        glUniform1i(uniform_wave, 1);
        glUniform1i(uniform_delta, elapsed);
        glBindTexture(GL_TEXTURE_2D, tex_flag);
        glDrawArrays(GL_TRIANGLES, wrapper_vertex_amount, 6);
        glUniform1i(uniform_wave, 0);

        //gui render
        glUniform1i(uniform_gui, 1);
        mat4 gui_projection = translate(mat4(1), vec3(-1, 1, 0));
        mat4 gui_view = mat4(1);
        gui_view[0][0] *= (float)height / (float)width;
        mat4 gui_model = translate(mat4(1), vec3(1, -1, 0));
        glUniformMatrix4fv(uniform_projection, 1, GL_FALSE, &gui_projection[0][0]);
        glUniformMatrix4fv(uniform_view, 1, GL_FALSE, &gui_view[0][0]);
        glBindTexture(GL_TEXTURE_2D, tex_heart);
        for (int i = 0; i < heart_left; ++i) {
            glUniformMatrix4fv(uniform_model, 1, GL_FALSE, &gui_model[0][0]);
            glDrawArrays(GL_TRIANGLES, heart_pos, 6);
            gui_model = translate(gui_model, vec3(heart_w, 0, 0));
        }
        glUniform1i(uniform_gui, 0);

        glfwSwapBuffers(window);
        glfwPollEvents();
    }

    glfwTerminate();
}
